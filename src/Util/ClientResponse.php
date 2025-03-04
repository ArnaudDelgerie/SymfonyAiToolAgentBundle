<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use ArnaudDelgerie\AiToolAgent\DTO\Message;

class ClientResponse
{
    public function __construct(
        public Message          $message,
        public AgentUsageReport $usageReport,
    ) {}
}