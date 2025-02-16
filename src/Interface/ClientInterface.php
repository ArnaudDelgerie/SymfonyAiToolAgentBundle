<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientResponse;

interface ClientInterface
{
    public function getClientEnum(): ClientEnum;

    public function chat(string $model, string $apiKey, array $messages, ?array $tools = [], ?float $temperature = 0.5, bool $onlyTool = true): ClientResponse;
}