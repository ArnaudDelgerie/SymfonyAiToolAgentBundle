<?php

namespace ArnaudDelgerie\AiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Util\AgentIO;
use ArnaudDelgerie\AiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\AiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\AiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\AiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\AiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\AiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\AiToolAgent\Util\ToolAgentHelper;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\AgentConfigInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;

class ConsoleToolAgent
{
    private bool $usageLog = true;

    private ClientConfigInterface $clientConfig;

    private AgentConfigInterface $agentConfig;

    private ClientInterface $client;

    private array $toolFunctions = [];

    private array $messages;

    private int $nbRequest = 0;

    private bool $userMessageRequired = true;

    private array $responseContent = [];

    private AgentUsageReport $usageReport;

    public function __construct(
        private ToolAgentHelper $toolAgentHelper,
        ClientConfigInterface   $clientConfig,
        AgentConfigInterface    $agentConfig,
    ) {
        $this->usageReport = new AgentUsageReport();
        $this->updateClientConfig($clientConfig);
        $this->updateAgentConfig($agentConfig);
    }

    public function addUserMessage(string $userPrompt): static
    {
        $this->nbRequest = 0;
        $this->userMessageRequired = false;
        $this->messages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::User, $userPrompt);

        return $this;
    }

    public function run(AgentIO $agentIO): AgentResponse
    {
        if (true === $this->userMessageRequired) {
            throw new RuntimeException('ConsoleToolAgent: "run" method cannot be called before "addUserMessage" method');
        }

        $clientResponse = $this->client->chat($this->messages, $this->toolFunctions);

        $this->usageReport->merge($clientResponse->usageReport);
        if ($this->usageLog) $agentIO->logUsage($this->usageReport);

        $completedToolCalls = $toolMessages = [];
        $assistantMessage = $clientResponse->message;
        /** @var MessageToolCall $toolCall */
        foreach ($assistantMessage->getToolCalls() as $toolCall) {
            $completedToolCalls[] = $toolCall;
            $functionName = $toolCall->getFunction()->getName();
            $args = $toolCall->getFunction()->getArguments();

            $toolFunctionManager = $this->toolAgentHelper->getConsoleToolFunctionManager($functionName);
            $validation = $toolFunctionManager->validate($args, $this->agentConfig->getContext(), $this->responseContent, $agentIO);

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
                $response = $toolFunctionManager->execute($args, $this->agentConfig->getContext(), $this->responseContent, $agentIO);
                $this->responseContent = $response->responseContent;
                $toolMessages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::Tool, $response->message, $functionName, $toolCall->getId());
                if ($response->stopRun) {
                    $assistantMessage->setToolCalls($completedToolCalls);
                    return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                }
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->clientConfig->getRequestLimit()) {
            $this->messages = array_merge($this->messages, [$assistantMessage], $toolMessages);
            return $this->run($agentIO);
        }

        return $this->toolStop($toolMessages, $assistantMessage, StopReasonEnum::RequestLimit, $this->nbRequest);
    }

    public function updateClientConfig(ClientConfigInterface $clientConfig): static
    {
        $this->clientConfig = $clientConfig;
        $this->client = $this->toolAgentHelper->getClient($clientConfig);

        return $this;
    }

    public function updateAgentConfig(AgentConfigInterface $agentConfig): static
    {
        $this->agentConfig = $agentConfig;
        $this->messages = [$this->toolAgentHelper->getMessage(MessageRoleEnum::System, $agentConfig->getSystemPrompt())];
        $this->toolFunctions = $this->toolAgentHelper->getConsoleToolFunctions($agentConfig->getFunctionNames(), $agentConfig->getContext());

        return $this;
    }

    public function setUsageLog(bool $usageLog): static
    {
        $this->usageLog = $usageLog;

        return $this;
    }

    private function toolStop(array $toolMessages, Message $assistantMessage, StopReasonEnum $stopReason, string $stopValue, ?StopStepEnum $stopReasonStep = null): AgentResponse
    {
        $this->userMessageRequired = true;
        $toolMessages[] = $this->toolAgentHelper->getMessage(MessageRoleEnum::Assistant, 'What would you like to do?');
        $this->messages = array_merge($this->messages, [$assistantMessage], $toolMessages);
        $stopReport = new AgentStopReport($stopReason, $stopValue, $stopReasonStep);
        return new AgentResponse($stopReport, $this->usageReport, $this->responseContent);
    }
}
