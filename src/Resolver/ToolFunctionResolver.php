<?php

namespace ArnaudDelgerie\AiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

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
        if (0 === count($names)) {
            throw new RuntimeException('You must provide at least one function name');
        }

        $toolFunctions = [];
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($contextData);
            }
        }

        if (count($names) !== count($toolFunctions)) {
            throw new RuntimeException(count($names) . ' function names provided, ' . count($toolFunctions) . ' functions found');            
        }

        return $toolFunctions;
    }

    /**
     * @return ToolFunction[]
     */
    public function getConsoleToolFunctions(array $names, array $contextData): array
    {
        if (0 === count($names)) {
            throw new RuntimeException('You must provide at least one function name');
        }

        $toolFunctions = [];
        /** @var ConsoleToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->consoleToolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction($contextData);
            }
        }

        if (count($names) !== count($toolFunctions)) {
            throw new RuntimeException(count($names) . ' function names provided, ' . count($toolFunctions) . ' functions found');            
        }

        return $toolFunctions;
    }
}