<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class AgentStopCommand
{
    public function __construct(
        public string  $word,
        public string  $description,
    ) {}
}