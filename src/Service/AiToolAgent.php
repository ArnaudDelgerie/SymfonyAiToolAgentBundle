<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Service;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\UsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\AiClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\AiClientResolver;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\AiClientInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ToolFunctionPropertyTypeEnum;

class AiToolAgent
{
    private AiClientInterface $client;

    private string $apiKey;

    private string $model;

    private array $toolFunctions;

    private array $messages;

    private float $temperature;

    private int $callLimit;

    private array $context;

    private array $taskReport;

    private UsageReport $usageReport;

    private int $nbCall = 0;

    private bool $initialized = false;

    public function __construct(
        private AiClientResolver     $clientResolver,
        private ValidatorInterface   $validator,
        private ToolFunctionResolver $toolFunctionResolver,
        private NormalizerInterface  $normalizer,
    ){}

    public function initClient(
        AiClientEnum $clientEnum,
        string       $apiKey,
        string       $model,
        string       $sysPrompt,
        string       $userPrompt,
        array        $functions,
        ?float       $temperature = 0.5,
        ?array       $context = [],
        ?array       $taskReport = [],
        ?UsageReport $usageReport = new UsageReport(),
        ?int         $callLimit = 10,
    ): self {
        $this->client = $this->clientResolver->getClient($clientEnum);
        $this->toolFunctions = $this->toolFunctionResolver->getToolFunctionsByName($functions);
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->messages = [
            (new Message())->setRole(MessageRoleEnum::System)->setContent($sysPrompt),
            (new Message())->setRole(MessageRoleEnum::User)->setContent($userPrompt),
        ];
        $this->temperature = $temperature;
        $this->context = $context;
        $this->taskReport = $taskReport;
        $this->usageReport = $usageReport;
        $this->callLimit = $callLimit;

        $this->validateToolFunctions();
        $this->addCompletedTasksToolFunction();

        $this->initialized = true;

        return $this;
    }

    public function run(): self
    {
        if (false === $this->initialized) {
            throw new RuntimeException('AiToolAgent: "run" method cannot be called before "initClient" method');
        }

        $message = $this->client->chat($this->model, $this->apiKey, $this->messages, $this->toolFunctions, $this->temperature, $this->usageReport);
        $this->messages[] = $message;

        /** @var MessageToolCall $toolCall */
        foreach ($message->getToolCalls() as $toolCall) {
            $functionName = $toolCall->getFunction()->getName();
            $arguments = $toolCall->getFunction()->getArguments();
            if ($functionName === 'completed_tasks') {
                return $this;
            }

            $toolFunction = $this->toolFunctionResolver->getToolFunctionManagerByName($functionName);
            $content = $toolFunction->execute($arguments, $this->context, $this->taskReport);
            $this->messages[] = (new Message())
                ->setRole(MessageRoleEnum::Tool)
                ->setContent($content)
                ->setName($functionName)
                ->setToolCallId($toolCall->getId());
        }

        $this->nbCall = $this->nbCall + 1;
        if ($this->nbCall <= $this->callLimit) {
            return $this->run();
        }

        return $this;
    }

    public function getTaskReport(): array
    {
        return $this->taskReport;
    }

    public function getUsageReport(): UsageReport
    {
        return $this->usageReport;
    }

    private function validateToolFunctions(): void
    {
        $errors = $this->validator->validate($this->toolFunctions);
        foreach ($errors as $error) {
            throw new RuntimeException('  - Tool' . $error->getPropertyPath() . ': ' . $error->getMessage());
        }
    }

    private function addCompletedTasksToolFunction(): void
    {
        $successProperty = (new ToolFunctionProperty())
            ->setType(ToolFunctionPropertyTypeEnum::Boolean)
            ->setDescription('True if all tasks have been successfully completed');

        $stopToolFunction = (new ToolFunction())
            ->setName('completed_tasks')
            ->setDescription('Call this function when all tasks requested by the user have been completed')
            ->setProperties(['success' => $successProperty]);

        $this->toolFunctions[] = $stopToolFunction;
    }
}