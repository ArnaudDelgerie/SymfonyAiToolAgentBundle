<?php

namespace ArnaudDelgerie\AiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\AiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\AiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\AiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\AiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\AiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\AiToolAgent\Util\ToolAgentHelper;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;

class ToolAgent
{
    private ClientInterface $client;

    private array $toolFunctions = [];

    private array $messages = [];

    private bool $initialized = false;

    private int $nbRequest = 0;

    private array $responseContent = [];

    private AgentUsageReport $usageReport;

    public function __construct(
        private ToolAgentHelper  $toolAgentHelper,
        private ClientConfig     $clientConfig,
        private array            $context,
    ) {
        $this->usageReport = new AgentUsageReport();
        $this->clientConfig->toolOnly = true;
        $this->client = $this->toolAgentHelper->getClient($this->clientConfig);
    }

    public function init(array $functionNames, string $sysPrompt, ?string $userPrompt = null, array $images = []): static 
    {
        if (null === $userPrompt && 0 === count($images)) {
            throw new RuntimeException('ToolAgent: "init" method must be called with $userPrompt !== null or count($images) > 0');
        }

        $this->toolFunctions = $this->toolAgentHelper->getToolFunctions($functionNames, $this->context);
        $this->messages = [
            $this->toolAgentHelper->getMessage(MessageRoleEnum::System, $sysPrompt),
            $this->toolAgentHelper->getMessage(MessageRoleEnum::User, $userPrompt ?? "", null, null, $images)
        ];
        $this->initialized = true;

        return $this;
    }

    public function run(): AgentResponse
    {
        if (false === $this->initialized) {
            throw new RuntimeException('ToolAgent: "run" method cannot be called before "init" method');
        }

        $clientResponse = $this->client->chat($this->messages, $this->toolFunctions);
        $this->usageReport->merge($clientResponse->usageReport);
        $message = $clientResponse->message;
        $this->messages[] = $message;

        /** @var MessageToolCall $toolCall */
        foreach ($message->getToolCalls() as $toolCall) {
            $functionName = $toolCall->getFunction()->getName();
            $arguments = $toolCall->getFunction()->getArguments();

            $toolFunctionManager = $this->toolAgentHelper->getToolFunctionManager($functionName);
            $response = $toolFunctionManager->execute($arguments, $this->context, $this->responseContent);

            $this->responseContent = $response->responseContent;
            $this->messages[] = (new Message())
                ->setRole(MessageRoleEnum::Tool)
                ->setContent($response->message)
                ->setName($functionName)
                ->setToolCallId($toolCall->getId());

            if ($response->stopRun) {
                $this->initialized = false;
                $stopReport = new AgentStopReport(StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                return new AgentResponse($stopReport, $this->usageReport, $this->responseContent);
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->clientConfig->requestLimit) {
            return $this->run();
        }

        $stopReport = new AgentStopReport(StopReasonEnum::RequestLimit, $this->nbRequest);
        return new AgentResponse($stopReport, $this->usageReport, $this->responseContent);
    }
}