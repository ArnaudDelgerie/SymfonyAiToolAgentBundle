<?php

namespace ArnaudDelgerie\AiToolAgent\Interface;

use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;

interface ToolFunctionManagerInterface
{
    public static function getName(): string;

    public function getToolFunction(array $context): ToolFunction;

    public function execute(array $ars, array $context, array $responseContent): ToolResponse;
}