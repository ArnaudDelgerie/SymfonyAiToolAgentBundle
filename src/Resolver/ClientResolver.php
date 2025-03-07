<?php

namespace ArnaudDelgerie\AiToolAgent\Resolver;

use RuntimeException;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;

class ClientResolver
{
    /** @param iterable<ClientInterface> $clients */
    public function __construct(private iterable $clients) {}

    public function getClient(ClientConfigInterface $clientConfig): ClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->getClientEnum() === $clientConfig->getClientEnum()) {
                $client->setConfig($clientConfig);
                return $client;
            }
        }

        throw new RuntimeException($clientConfig->getClientEnum()->value . ' client not found');
    }
}