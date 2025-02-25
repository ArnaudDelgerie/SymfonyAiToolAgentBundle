<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class ToolFunctionResolver
{
    public function __construct(
        private iterable $toolFunctionManagers,
        private iterable $consoleToolFunctionManagers
    ) {}

    public function getToolFunctionManager(string $name): ToolFunctionManagerInterface
    {
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if ($toolFunctionManager::getName() === $name) return $toolFunctionManager;
        }

        throw new RuntimeException($name . ' toolFunctionManager not found');
    }

    public function getConsoleToolFunctionManager(string $name): ConsoleToolFunctionManagerInterface
    {
        /** @var ConsoleToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->consoleToolFunctionManagers as $toolFunctionManager) {
            if ($toolFunctionManager::getName() === $name) return $toolFunctionManager;
        }

        throw new RuntimeException($name . ' toolFunctionManager not found');
    }

    /**
     * @return ToolFunction[]
     */
    public function getToolFunctions(array $names, array $contextData): array
    {
        $toolFunctions = [];
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($contextData);
            }
        }

        if (0 === count($toolFunctions)) {
            throw new RuntimeException('No toolFunction found for names ' . implode(', ', $names));            
        }

        return $toolFunctions;
    }

    /**
     * @return ToolFunction[]
     */
    public function getConsoleToolFunctions(array $names, array $contextData): array
    {
        $toolFunctions = [];
        /** @var ConsoleToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->consoleToolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($contextData);
            }
        }

        if (0 === count($toolFunctions)) {
            throw new RuntimeException('No toolFunction found for names ' . implode(', ', $names));            
        }

        return $toolFunctions;
    }
}