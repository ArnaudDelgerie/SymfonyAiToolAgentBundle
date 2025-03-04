<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use ArnaudDelgerie\AiToolAgent\Agent\ToolAgent;
use ArnaudDelgerie\AiToolAgent\Agent\ConsoleToolAgent;

class ToolAgentProvider
{
    public function __construct(private ToolAgentHelper $toolAgentHelper) {}

    public function createToolAgent(ClientConfig $clientConfig, array $context = []): ToolAgent
    {
        return new ToolAgent($this->toolAgentHelper, $clientConfig, $context);
    }

    public function createConsoleToolAgent(ClientConfig $clientConfig, array $context = []): ConsoleToolAgent
    {
        return new ConsoleToolAgent($this->toolAgentHelper, $clientConfig, $context);
    }
}