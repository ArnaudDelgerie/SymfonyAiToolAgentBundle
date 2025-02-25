<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolAgentHelper;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;

class ToolAgent
{
    private ClientInterface $client;

    private array $toolFunctions = [];

    private array $messages = [];

    private bool $initialized = false;

    private int $nbRequest = 0;

    public function __construct(
        private ToolAgentHelper  $toolAgentHelper,
        private ClientConfig     $clientConfig,
        private array            $context,
        private AgentUsageReport $usageReport,
    ) {
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
            $this->toolAgentHelper->getMessage(MessageRoleEnum::User, $userPrompt, null, null, $images)
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
            $response = $toolFunctionManager->execute($arguments, $this->context);

            $this->context = $response->context;
            $this->messages[] = (new Message())
                ->setRole(MessageRoleEnum::Tool)
                ->setContent($response->message)
                ->setName($functionName)
                ->setToolCallId($toolCall->getId());

            if ($response->stopRun) {
                $this->initialized = false;
                $stopReport = new AgentStopReport(StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                return new AgentResponse($stopReport, $this->usageReport, $this->context);
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->clientConfig->requestLimit) {
            return $this->run();
        }

        $stopReport = new AgentStopReport(StopReasonEnum::RequestLimit, (string) $this->nbRequest);
        return new AgentResponse($stopReport, $this->usageReport, $this->context);
    }
}