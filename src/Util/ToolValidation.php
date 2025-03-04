<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

class ToolValidation
{
    public function __construct(
        public array  $args,
        public array  $responseContent,
        public bool   $isExecutable = true,
        public string $message = 'This task could not be carried out',
        public bool   $stopSequence = false,
        public bool   $stopRun = false,
    ) {}
}