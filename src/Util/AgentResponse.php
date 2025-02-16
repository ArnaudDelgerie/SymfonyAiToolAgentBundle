<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class AgentResponse
{
    public function __construct(
        public AgentStopReport $stopReport,
        public AgentUsageReport     $usageReport,
        public array           $taskReport,
    ) {}
}