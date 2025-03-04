<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

class AgentResponse
{
    public function __construct(
        public AgentStopReport  $stopReport,
        public AgentUsageReport $usageReport,
        public array            $content,
    ) {}
}