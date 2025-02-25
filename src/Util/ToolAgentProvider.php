<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use ArnaudDelgerie\SymfonyAiToolAgent\Agent\ToolAgent;
use ArnaudDelgerie\SymfonyAiToolAgent\Agent\ConsoleToolAgent;

class ToolAgentProvider
{
    public function __construct(private ToolAgentHelper $toolAgentHelper) {}

    public function createToolAgent(ClientConfig $clientConfig, array $context = [], AgentUsageReport $usageReport = new AgentUsageReport()): ToolAgent
    {
        return new ToolAgent($this->toolAgentHelper, $clientConfig, $context, $usageReport);
    }

    public function createConsoleToolAgent(ClientConfig $clientConfig, array $context = [], AgentUsageReport $usageReport = new AgentUsageReport()): ConsoleToolAgent
    {
        return new ConsoleToolAgent($this->toolAgentHelper, $clientConfig, $context, $usageReport);
    }
}