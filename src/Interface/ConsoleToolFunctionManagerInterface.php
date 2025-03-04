<?php

namespace ArnaudDelgerie\AiToolAgent\Interface;

use ArnaudDelgerie\AiToolAgent\Util\AgentIO;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\Util\ToolValidation;

interface ConsoleToolFunctionManagerInterface
{
    public static function getName(): string;

    public function getToolFunction(array $context): ToolFunction;

    public function validate(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolValidation;

    public function execute(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolResponse;
}