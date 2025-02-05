<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;

class ToolFunctionResolver
{
    public function __construct(private iterable $toolFunctionManagers) {}

    public function getToolFunctionManagerByName(string $name): ToolFunctionManagerInterface
    {
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if ($toolFunctionManager::getName() === $name) return $toolFunctionManager;
        }

        throw new RuntimeException($name . ' toolFunctionManager not found');
    }

    /** @return ToolFunctionManagerInterface[] */
    public function getToolFunctionsByName(array $names): array
    {
        $toolFunctions = [];
        /** @var ToolFunctionManagerInterface $toolFunctionManager */
        foreach ($this->toolFunctionManagers as $toolFunctionManager) {
            if (in_array($toolFunctionManager::getName(), $names, true)) {
                $toolFunctions[] = $toolFunctionManager->getToolFunction();
            }
        }

        if (0 === count($toolFunctions)) {
            throw new RuntimeException('No toolFunction found for names ' . implode(', ', $names));            
        }

        return $toolFunctions;
    }
}