<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class ToolResponse
{
    public function __construct(
        public string $message = 'Task completed',
        public bool   $stop = false,
    ) {}
}