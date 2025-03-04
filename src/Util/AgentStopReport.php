<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use ArnaudDelgerie\AiToolAgent\Enum\StopStepEnum;
use ArnaudDelgerie\AiToolAgent\Enum\StopReasonEnum;

class AgentStopReport
{
    public function __construct(
        public StopReasonEnum $stopReason,
        public mixed         $value,
        public ?StopStepEnum  $step = null,
    ) {}
}