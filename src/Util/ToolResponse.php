<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

class ToolResponse
{
    public function __construct(
        public array  $responseContent,
        public string $message = 'Task completed',
        public bool   $stopRun = false,
    ) {}
}