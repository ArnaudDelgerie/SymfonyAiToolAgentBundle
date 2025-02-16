<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;

class ClientResponse
{
    public function __construct(
        public Message     $message,
        public AgentUsageReport $usageReport,
    ) {}
}