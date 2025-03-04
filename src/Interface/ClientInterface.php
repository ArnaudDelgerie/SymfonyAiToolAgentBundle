<?php

namespace ArnaudDelgerie\AiToolAgent\Interface;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\AiToolAgent\Util\ClientResponse;

interface ClientInterface
{
    public function getClientEnum(): ClientEnum;

    public function setConfig(ClientConfig $config): void;

    public function chat(array  $messages, array  $tools = []): ClientResponse;
}