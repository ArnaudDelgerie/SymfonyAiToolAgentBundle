<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class ToolResponse
{
    public function __construct(
        public array  $context,
        public string $message = 'Task completed',
        public bool   $stopRun = false,
    ) {}
}