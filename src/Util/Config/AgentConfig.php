<?php

namespace ArnaudDelgerie\AiToolAgent\Util\Config;

use ArnaudDelgerie\AiToolAgent\Interface\AgentConfigInterface;

class AgentConfig implements AgentConfigInterface
{
    public function __construct(
        private string $systemPrompt,
        private array  $functionNames,
        private array  $context = [],
    ) {}

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function getFunctionNames(): array
    {
        return $this->functionNames;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}