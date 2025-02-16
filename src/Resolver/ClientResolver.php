<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use RuntimeException;

class ClientResolver
{
    /** @param iterable<ClientInterface> $clients */
    public function __construct(private iterable $clients) {}

    public function getClient(ClientEnum $clientEnum): ClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->getClientEnum() === $clientEnum) return $client;
        }

        throw new RuntimeException($clientEnum->value . ' client not found');
    }
}