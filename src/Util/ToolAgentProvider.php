<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use ArnaudDelgerie\AiToolAgent\Agent\ToolAgent;
use ArnaudDelgerie\AiToolAgent\Agent\ConsoleToolAgent;
use ArnaudDelgerie\AiToolAgent\Interface\AgentConfigInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;

class ToolAgentProvider
{
    public function __construct(private ToolAgentHelper $toolAgentHelper) {}

    public function createToolAgent(ClientConfigInterface $clientConfig, AgentConfigInterface $agentConfig): ToolAgent
    {
        return new ToolAgent($this->toolAgentHelper, $clientConfig, $agentConfig);
    }

    public function createConsoleToolAgent(ClientConfigInterface $clientConfig, AgentConfigInterface $agentConfig): ConsoleToolAgent
    {
        return new ConsoleToolAgent($this->toolAgentHelper, $clientConfig, $agentConfig);
    }
}