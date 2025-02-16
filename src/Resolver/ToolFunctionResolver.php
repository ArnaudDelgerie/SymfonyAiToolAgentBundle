<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ConsoleToolFunctionManagerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;

class ToolFunctionResolver
{
    public function __construct(
        private iterable $toolFunctionManagers,
        private iterable $interactiveCommandToolFunctionManagers
    ) {}

    public function getToolFunctionManager(string $name): ToolFunctionManagerInterface
    {
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if ($toolFunctionManager::getName() === $name) return $toolFunctionManager;
        }

        throw new RuntimeException($name . ' toolFunctionManager not found');
    }

    public function getInteractiveCommandToolFunctionManager(string $name): ConsoleToolFunctionManagerInterface
    {
        /** @var ConsoleToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->interactiveCommandToolFunctionManagers as $toolFunctionManager) {
            if ($toolFunctionManager::getName() === $name) return $toolFunctionManager;
        }

        throw new RuntimeException($name . ' toolFunctionManager not found');
    }

    /**
     * @return ToolFunction[] $toolFunctions
     */
    public function getToolFunctions(array $names, array $context): array
    {
        $toolFunctions = [];
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($context);
            }
        }

        if (0 === count($toolFunctions)) {
            throw new RuntimeException('No toolFunction found for names ' . implode(', ', $names));            
        }

        return $toolFunctions;
    }

    /**
     * @return ToolFunction[] $toolFunctions
     */
    public function getInteractiveCommandToolFunctions(array $names, array $context): array
    {
        $toolFunctions = [];
        /** @var ConsoleToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->interactiveCommandToolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($context);
            }
        }

        if (0 === count($toolFunctions)) {
            throw new RuntimeException('No toolFunction found for names ' . implode(', ', $names));            
        }

        return $toolFunctions;
    }
}