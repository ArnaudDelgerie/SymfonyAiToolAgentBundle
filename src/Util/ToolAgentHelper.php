<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Enum\MessageRoleEnum;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\AiToolAgent\Resolver\ClientResolver;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Resolver\ToolFunctionResolver;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

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

    public function getMessage(MessageRoleEnum $role, string $content, ?string $functionName = null, ?string $toolCallId = null, array $images = []): Message
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