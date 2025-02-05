<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\AiClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\AiClientInterface;
use RuntimeException;

class AiClientResolver
{
    /** @param iterable<AiClientInterface> $clients */
    public function __construct(private iterable $clients) {}

    public function getClient(AiClientEnum $clientEnum): AiClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->getClientEnum() === $clientEnum) return $client;
        }

        throw new RuntimeException($clientEnum->value . ' client not found');
    }
}