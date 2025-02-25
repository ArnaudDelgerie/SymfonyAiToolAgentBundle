<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentIO;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolAgentHelper;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopCommand;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;

class ConsoleToolAgent
{
    private bool $usageLog = true;

    private array $stopCommands = [];

    private ClientInterface $client;

    private array $toolFunctions = [];

    private array $messages;

    private bool $initialized = false;

    private int $nbRequest = 0;

    private bool $userPromptRequired = true;

    public function __construct(
        private ToolAgentHelper  $toolAgentHelper,
        private ClientConfig     $clientConfig,
        private array            $context,
        private AgentUsageReport $usageReport,
    ) {
        $this->client = $this->toolAgentHelper->getClient($this->clientConfig);
    }

    public function init(array $functionNames, string $sysPrompt, ?string $userPrompt = null): static 
    {
        $this->toolFunctions = $this->toolAgentHelper->getConsoleToolFunctions($functionNames, $this->context);
        $this->messages = [$this->toolAgentHelper->getMessage(MessageRoleEnum::System, $sysPrompt)];
        if ($userPrompt) {
            $this->userPromptRequired = false;
            $this->messages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::User, $userPrompt);
        }
        $this->initialized = true;

        return $this;
    }

    public function run(AgentIO $agentIO, ?string $question = 'What would you like to do ?'): AgentResponse
    {
        if (false === $this->initialized) {
            throw new RuntimeException('ConsoleToolAgent: "run" method cannot be called before "init" method');
        }

        if ($this->userPromptRequired) {
            $this->nbRequest = 0;
            if (count($this->stopCommands) > 0) {
                $finalQuestion = $question . ' (you can also enter ? to see availables commands)';
            }
            $userPrompt = $agentIO->ask($finalQuestion ?? $question);
            if (count($this->stopCommands) > 0 && $userPrompt === '?') {
                $agentIO->showAvailablesCommands($this->stopCommands);
                return $this->run($agentIO, $question);
            }
            if (null === $userPrompt || $this->isStopCommand($userPrompt)) {
                $stopReport = new AgentStopReport(StopReasonEnum::Command, $userPrompt ?? '');
                return new AgentResponse($stopReport, $this->usageReport, $this->context);
            }
            $this->messages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::User, $userPrompt);
            $this->userPromptRequired = false;
        }

        $clientResponse = $this->client->chat($this->messages, $this->toolFunctions);
        $this->usageReport->merge($clientResponse->usageReport);
        $assistantMessage = $clientResponse->message;

        if ($this->usageLog) {
            $agentIO->logUsage($this->usageReport);
        }

        if (null === $assistantMessage->getToolCalls() || count($assistantMessage->getToolCalls()) === 0) {
            $this->messages[] = $assistantMessage;
            $this->userPromptRequired = true;
            $agentIO->text($assistantMessage->getContent());
            return $this->run($agentIO, $question);
        }

        $completedToolCalls = $toolMessages = [];
        /** @var MessageToolCall $toolCall */
        foreach ($assistantMessage->getToolCalls() as $toolCall) {
            $completedToolCalls[] = $toolCall;
            $functionName = $toolCall->getFunction()->getName();
            $args = $toolCall->getFunction()->getArguments();

            $toolFunctionManager = $this->toolAgentHelper->getConsoleToolFunctionManager($functionName);
            $validation = $toolFunctionManager->validate($args, $this->context, $agentIO);

            $args = $validation->args;
            $this->context = $validation->context;

            if (!$validation->isExecutable) {
                $toolMessages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::Tool, $validation->message, $functionName, $toolCall->getId());
                if ($validation->stopSequence) {
                    $assistantMessage->setToolCalls($completedToolCalls);
                    if ($validation->stopRun) {
                        return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::Function, $functionName, StopStepEnum::Call);
                    }
                    break;
                }
            } else {
                $response = $toolFunctionManager->execute($args, $this->context, $agentIO);
                $this->context = $response->context;
                $toolMessages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::Tool, $response->message, $functionName, $toolCall->getId());
                if ($response->stopRun) {
                    $assistantMessage->setToolCalls($completedToolCalls);
                    return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                }
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->clientConfig->requestLimit) {
            $this->messages = array_merge($this->messages, [$assistantMessage], $toolMessages);
            return $this->run($agentIO, $question);
        }

        $agentIO->alert('Request limit reached');
        $assistantMessage->setToolCalls($completedToolCalls);
        return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::RequestLimit, (string) $this->nbRequest);
    }

    public function setUsageLog(bool $usageLog): static
    {
        $this->usageLog = $usageLog;

        return $this;
    }

    public function setStopCommands(array $stopCommands): static
    {
        foreach ($stopCommands as $stopCommand) {
            if (!$stopCommand instanceof AgentStopCommand) {
                throw new RuntimeException('setStopCommands: $stopCommands must be an array of ' . AgentStopCommand::class);
            }
            if ($stopCommand->word === '?') {
                throw new RuntimeException('setStopCommands: "?" is a reserved AgentStopCommand->word');
            }
        }

        $this->stopCommands = $stopCommands;

        return $this;
    }

    private function toolStop(array $toolMessages, Message $assistantMessage, StopReasonEnum $stopReason, string $stopValue, ?StopStepEnum $stopReasonStep = null): AgentResponse
    {
        $this->userPromptRequired = true;
        $toolMessages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::Assistant, 'All tasks are completed, what would you like to do?');
        $this->messages = array_merge($this->messages, [$assistantMessage], $toolMessages);
        $stopReport = new AgentStopReport($stopReason, $stopValue, $stopReasonStep);
        return new AgentResponse($stopReport, $this->usageReport, $this->context);
    }

    private function isStopCommand(string $command): bool
    {
        /** @var AgentStopCommand $stopWord */
        foreach ($this->stopCommands as $stopCommand) {
            if ($command === $stopCommand->word) {
                return true;
            }
        }
        return false;
    }
}
