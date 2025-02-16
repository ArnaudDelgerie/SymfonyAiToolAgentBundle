<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentIO;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopCommand;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ClientResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;

class ConsoleToolAgent extends AbstractToolAgent
{
    private bool $toolOnly = true;

    private bool $usageLog = true;

    private array $stopCommands = [];

    public function __construct(
        ClientResolver       $clientResolver,
        ValidatorInterface   $validator,
        ToolFunctionResolver $toolFunctionResolver,
        NormalizerInterface  $normalizer,
    ) {
        parent::__construct($clientResolver, $validator, $toolFunctionResolver, $normalizer);
    }

    public function init(
        ClientEnum  $clientEnum,
        string      $apiKey,
        string      $model,
        array       $functions,
        string      $sysPrompt,
        ?string     $userPrompt = null
    ): static {
        $this->sysPrompt = $sysPrompt;
        $this->initClient($clientEnum);
        $this->initToolFunctions('console', $functions);
        $this->initMessages($sysPrompt, $userPrompt);
        $this->initClientParameters($apiKey, $model);
        $this->initialized = true;

        return $this;
    }

    public function setToolOnly(bool $toolOnly): static
    {
        $this->toolOnly = $toolOnly;

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
                return new AgentResponse($stopReport, $this->usageReport, $this->taskReport);
            }
            $this->messages[] = $this->getMessage(MessageRoleEnum::User, $userPrompt);
            $this->userPromptRequired = false;
        }

        $clientResponse = $this->client->chat($this->model, $this->apiKey, $this->messages, $this->toolFunctions, $this->temperature, $this->toolOnly);
        $this->usageReport->merge($clientResponse->usageReport);
        $message = $clientResponse->message;

        if ($this->usageLog) {
            $agentIO->logUsage($this->usageReport);
        }

        if (null === $message->getToolCalls() || count($message->getToolCalls()) === 0) {
            $this->messages[] = $message;
            $this->userPromptRequired = true;
            $agentIO->text($message->getContent());
            return $this->run($agentIO, $question);
        }

        $completedToolCalls = $toolMessages = [];
        /** @var MessageToolCall $toolCall */
        foreach ($message->getToolCalls() as $toolCall) {
            $completedToolCalls[] = $toolCall;
            $functionName = $toolCall->getFunction()->getName();
            $arguments = $toolCall->getFunction()->getArguments();
            $toolFunctionManager = $this->toolFunctionResolver->getInteractiveCommandToolFunctionManager($functionName);
            $validation = $toolFunctionManager->validate($arguments, $this->context, $this->taskReport, $agentIO);
            if (!$validation->isExecutable) {
                $toolMessages[] = $this->getMessage(MessageRoleEnum::Tool, $validation->message, $functionName, $toolCall->getId());
                if (!$validation->continueSequence) {
                    $message->setToolCalls($completedToolCalls);
                    if ($validation->stop) {
                        return $this->toolStop($toolMessages, $message, StopReasonEnum::Function, $functionName, StopStepEnum::Call);
                    }
                    break;
                }
            } else {
                $response = $toolFunctionManager->execute($arguments, $this->context, $this->taskReport, $agentIO);
                $toolMessages[] = $this->getMessage(MessageRoleEnum::Tool, $response->message, $functionName, $toolCall->getId());
                if ($response->stop) {
                    $message->setToolCalls($completedToolCalls);
                    return $this->toolStop($toolMessages, $message, StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                }
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->requestLimit) {
            $this->messages = array_merge($this->messages, [$message], $toolMessages);
            return $this->run($agentIO, $question);
        }

        $agentIO->alert('Request limit reached');
        $message->setToolCalls($completedToolCalls);
        return $this->toolStop($toolMessages, $message, StopReasonEnum::RequestLimit, (string) $this->nbRequest);
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

    public function clearMessages(): static
    {
        $this->messages = [$this->getMessage(MessageRoleEnum::System, $this->sysPrompt)];

        return $this;
    }

    private function toolStop(array $toolMessages, Message $message, StopReasonEnum $stopReason, string $stopValue, ?StopStepEnum $stopReasonStep = null): AgentResponse
    {
        $this->userPromptRequired = true;
        $toolMessages[] = $this->getMessage(MessageRoleEnum::Assistant, 'All tasks are completed, what would you like to do?');
        $this->messages = array_merge($this->messages, [$message], $toolMessages);
        $stopReport = new AgentStopReport($stopReason, $stopValue, $stopReasonStep);
        return new AgentResponse($stopReport, $this->usageReport, $this->taskReport);
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
