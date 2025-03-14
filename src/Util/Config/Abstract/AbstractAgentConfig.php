<?php

namespace ArnaudDelgerie\AiToolAgent\Util\Config\Abstract;

use ArnaudDelgerie\AiToolAgent\Interface\AgentConfigInterface;

abstract class AbstractAgentConfig implements AgentConfigInterface
{
    public function getSystemPrompt(): string
    {
        return 'You are a helpful assistant';
    }

    public function getFunctionNames(): array
    {
        return [];
    }

    public function getContext(): array
    {
        return [];
    }
}