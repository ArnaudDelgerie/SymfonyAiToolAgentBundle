<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Interface;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientResponse;

interface ClientInterface
{
    public function getClientEnum(): ClientEnum;

    public function setConfig(ClientConfig $config): void;

    public function chat(array  $messages, array  $tools = []): ClientResponse;
}