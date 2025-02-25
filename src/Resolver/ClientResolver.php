<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;

class ClientResolver
{
    /** @param iterable<ClientInterface> $clients */
    public function __construct(private iterable $clients) {}

    public function getClient(ClientConfig $clientConfig): ClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->getClientEnum() === $clientConfig->clientEnum) {
                $client->setConfig($clientConfig);
                return $client;
            }
        }

        throw new RuntimeException($clientConfig->clientEnum->value . ' client not found');
    }
}