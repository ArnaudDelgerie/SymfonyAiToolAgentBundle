<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\UsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\AiClientEnum;

interface AiClientInterface
{
    public function getClientEnum(): AiClientEnum;

    public function chat(string $model, string $apiKey, array $messages, ?array $tools = [], ?float $temperature = 0.5, ?UsageReport &$usageReport = new UsageReport()): Message;
}