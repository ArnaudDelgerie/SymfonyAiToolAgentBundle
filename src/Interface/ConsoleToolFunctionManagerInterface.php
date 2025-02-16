<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentIO;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolValidation;

interface ConsoleToolFunctionManagerInterface
{
    public static function getName(): string;

    public function getToolFunction(array $context): ToolFunction;

    public function validate(array &$args, array &$context, array &$taskReport, AgentIO $agentIO): ToolValidation;

    public function execute(array $args, array &$context, array &$taskReport, AgentIO $agentIO): ToolResponse;
}