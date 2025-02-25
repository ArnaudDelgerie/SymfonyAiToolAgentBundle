<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ClientResolver;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class ToolAgentHelper
{
    public function __construct(
        private ClientResolver       $clientResolver,
        private ToolFunctionResolver $toolFunctionResolver,
        private ValidatorInterface   $validator,
    ) {}

    public function getClient(ClientConfig $clientConfig): ClientInterface
    {
        return $this->clientResolver->getClient($clientConfig);
    }

    /**
     * @return ToolFunction[]
     */
    public function getToolFunctions(array $functionNames, array $contextData): array
    {
        $functions = $this->toolFunctionResolver->getToolFunctions($functionNames, $contextData);
        $this->validateToolFunctions($functions);

        return $functions;
    }

    /**
     * @return ToolFunction[]
     */
    public function getConsoleToolFunctions(array $functionNames, array $contextData): array
    {
        $functions = $this->toolFunctionResolver->getConsoleToolFunctions($functionNames, $contextData);
        $this->validateToolFunctions($functions);

        return $functions;
    }

    public function getToolFunctionManager(string $functionName): ToolFunctionManagerInterface
    {
        return $this->toolFunctionResolver->getToolFunctionManager($functionName);
    }

    public function getConsoleToolFunctionManager(string $functionName): ConsoleToolFunctionManagerInterface
    {
        return $this->toolFunctionResolver->getConsoleToolFunctionManager($functionName);
    }

    public function getMessage(MessageRoleEnum $role, ?string $content = null, ?string $functionName = null, ?string $toolCallId = null, array $images = []): Message
    {
        return (new Message())
            ->setRole($role)
            ->setContent($content)
            ->setName($functionName)
            ->setToolCallId($toolCallId)
            ->setImages($images);
    }

    private function validateToolFunctions(array $toolFunctions): void
    {
        $errors = $this->validator->validate($toolFunctions);
        foreach ($errors as $error) {
            throw new RuntimeException('  - Tool' . $error->getPropertyPath() . ': ' . $error->getMessage());
        }
    }
}