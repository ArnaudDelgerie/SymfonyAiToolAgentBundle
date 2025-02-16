<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Agent;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ClientResolver;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;

abstract class AbstractToolAgent
{
    protected ClientInterface $client;

    protected string $apiKey;

    protected string $model;

    protected array $toolFunctions;

    protected string $sysPrompt;

    protected array $messages;

    protected float $temperature = 0.5;

    protected int $requestLimit = 10;

    protected array $context = [];

    protected array $taskReport = [];

    protected AgentUsageReport $usageReport;

    protected int $nbRequest = 0;

    protected bool $initialized = false;

    protected bool $userPromptRequired = true;

    public function __construct(
        protected ClientResolver     $clientResolver,
        protected ValidatorInterface   $validator,
        protected ToolFunctionResolver $toolFunctionResolver,
        protected NormalizerInterface  $normalizer,
    ){
        $this->usageReport = new AgentUsageReport();
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function setTaskReport(array $taskReport): static
    {
        $this->taskReport = $taskReport;

        return $this;
    }

    public function setUsageReport(AgentUsageReport $usageReport): static
    {
        $this->usageReport = $usageReport;

        return $this;
    }

    public function setRequestLimit(int $requestLimit): static
    {
        $this->requestLimit = $requestLimit;

        return $this;
    }

    protected function initClient(ClientEnum $clientEnum): void
    {
        $this->client = $this->clientResolver->getClient($clientEnum);
    }

    protected function initToolFunctions(string $type, array $functions): void
    {
        if ($type === 'console') {
            $this->toolFunctions = $this->toolFunctionResolver->getInteractiveCommandToolFunctions($functions, $this->context);
        } else {
            $this->toolFunctions = $this->toolFunctionResolver->getToolFunctions($functions, $this->context);
        }
        $this->validateToolFunctions();
    }

    protected function initMessages(string $sysPrompt, ?string $userPrompt = null): void
    {
        $this->messages = [(new Message())->setRole(MessageRoleEnum::System)->setContent($sysPrompt)];

        if (null !== $userPrompt) {
            $this->userPromptRequired = false;
            $this->messages[] = (new Message())->setRole(MessageRoleEnum::User)->setContent($userPrompt);
        }
    }

    protected function initClientParameters(string $apiKey, string $model): void
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    protected function getMessage(MessageRoleEnum $role, string $content, ?string $functionName = null, ?string $toolCallId = null): Message
    {
        return (new Message())
            ->setRole($role)
            ->setContent($content)
            ->setName($functionName)
            ->setToolCallId($toolCallId);
    }

    private function validateToolFunctions(): void
    {
        $errors = $this->validator->validate($this->toolFunctions);
        foreach ($errors as $error) {
            throw new RuntimeException('  - Tool' . $error->getPropertyPath() . ': ' . $error->getMessage());
        }
    }
}