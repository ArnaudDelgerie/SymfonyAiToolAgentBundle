<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class ToolValidation
{
    public function __construct(
        public bool   $isExecutable = true,
        public string $message = 'This task could not be carried out',
        public bool   $continueSequence = true,
        public bool   $stop = false,
    ) {}
}