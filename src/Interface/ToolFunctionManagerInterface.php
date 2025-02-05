<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;

interface ToolFunctionManagerInterface
{
    public static function getName(): string;

    public function getToolFunction(): ToolFunction;

    public function execute(array $arguments, array $context, array &$taskReport): string;
}