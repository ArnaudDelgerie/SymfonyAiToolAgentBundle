<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

class AgentStopCommand
{
    public function __construct(
        public string  $word,
        public string  $description,
    ) {}
}