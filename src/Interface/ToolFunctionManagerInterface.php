<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolResponse;

interface ToolFunctionManagerInterface
{
    public static function getName(): string;

    public function getToolFunction(array $context): ToolFunction;

    public function execute(array $ars, array $context): ToolResponse;
}