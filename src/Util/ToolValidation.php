<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class ToolValidation
{
    public function __construct(
        public array  $args,
        public array  $context,
        public bool   $isExecutable = true,
        public string $message = 'This task could not be carried out',
        public bool   $stopSequence = false,
        public bool   $stopRun = false,
    ) {}
}