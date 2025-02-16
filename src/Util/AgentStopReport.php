<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopReasonEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\StopStepEnum;

class AgentStopReport
{
    public function __construct(
        public StopReasonEnum $stopReason,
        public string         $value,
        public ?StopStepEnum  $step = null,
    ) {}
}