<?php

namespace ArnaudDelgerie\AiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Util\AgentIO;
use ArnaudDelgerie\AiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\AiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\AiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\AiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\AiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\AiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\AiToolAgent\Util\ToolAgentHelper;
use ArnaudDelgerie\AiToolAgent\Util\AgentStopCommand;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;

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

    private array $responseContent = [];

    private AgentUsageReport $usageReport;

    public function __construct(
        private ToolAgentHelper  $toolAgentHelper,
        private ClientConfig     $clientConfig,
        private array            $context,
    ) {
        $this->usageReport = new AgentUsageReport();
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
                return new AgentResponse($stopReport, $this->usageReport, $this->responseContent);
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
            $validation = $toolFunctionManager->validate($args, $this->context, $this->responseContent, $agentIO);

            $args = $validation->args;
            $this->responseContent = $validation->responseContent;

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
                $response = $toolFunctionManager->execute($args, $this->context, $this->responseContent, $agentIO);
                $this->responseContent = $response->responseContent;
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
        return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::RequestLimit, $this->nbRequest);
    }

    public function updateClientConfig(ClientConfig $clientConfig): static
    {
        $this->clientConfig = $clientConfig;
        $this->client->setConfig($clientConfig);

        return $this;
    }

    public function updateToolFunctions(array $functionNames): static
    {
        $this->toolFunctions = $this->toolAgentHelper->getConsoleToolFunctions($functionNames, $this->context);

        return $this;
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
        return new AgentResponse($stopReport, $this->usageReport, $this->responseContent);
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
