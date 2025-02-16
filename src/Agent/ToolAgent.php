<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentStopReport;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ClientResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;

class ToolAgent extends AbstractToolAgent
{
    public function __construct(
        ClientResolver     $clientResolver,
        ValidatorInterface   $validator,
        ToolFunctionResolver $toolFunctionResolver,
        NormalizerInterface  $normalizer,
    ) {
        parent::__construct($clientResolver, $validator, $toolFunctionResolver, $normalizer);
    }

    public function init(
        ClientEnum $clientEnum,
        string       $apiKey,
        string       $model,
        array        $functions,
        string       $sysPrompt,
        string       $userPrompt
    ): static {
        $this->initClient($clientEnum);
        $this->initToolFunctions('std', $functions);
        $this->initMessages($sysPrompt, $userPrompt);
        $this->initClientParameters($apiKey, $model);
        $this->initialized = true;

        return $this;
    }

    public function run(): AgentResponse
    {
        if (false === $this->initialized) {
            throw new RuntimeException('"run" method cannot be called before "init" method');
        }

        $clientResponse = $this->client->chat($this->model, $this->apiKey, $this->messages, $this->toolFunctions, $this->temperature, true);
        $this->usageReport->merge($clientResponse->usageReport);
        $message = $clientResponse->message;
        $this->messages[] = $message;

        /** @var MessageToolCall $toolCall */
        foreach ($message->getToolCalls() as $toolCall) {
            $functionName = $toolCall->getFunction()->getName();
            $arguments = $toolCall->getFunction()->getArguments();
            $toolFunctionManager = $this->toolFunctionResolver->getToolFunctionManager($functionName);
            $response = $toolFunctionManager->execute($arguments, $this->context, $this->taskReport);
            $this->messages[] = (new Message())
                ->setRole(MessageRoleEnum::Tool)
                ->setContent($response->message)
                ->setName($functionName)
                ->setToolCallId($toolCall->getId());

            if ($response->stop) {
                $this->initialized = false;
                $stopReport = new AgentStopReport(StopReasonEnum::Function, $functionName, StopStepEnum::Execute);
                return new AgentResponse($stopReport, $this->usageReport, $this->taskReport);
            }
        }

        $this->nbRequest = $this->nbRequest + 1;
        if ($this->nbRequest < $this->requestLimit) {
            return $this->run();
        }

        $stopReport = new AgentStopReport(StopReasonEnum::RequestLimit, (string) $this->nbRequest);
        return new AgentResponse($stopReport, $this->usageReport, $this->taskReport);
    }
}