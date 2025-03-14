<?php

namespace ArnaudDelgerie\AiToolAgent\Util\Config;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;

class ClientConfig implements ClientConfigInterface
{
    public function __construct(
        private ClientEnum $clientEnum,
        private string     $apiKey,
        private string     $model,
        private float      $temperature = 1.0,
        private ?int       $requestLimit = null,
        private int        $timeout = 60,
        private int        $maxOutputToken = 8192,
    ) {}

    public function getClientEnum(): ClientEnum
    {
        return $this->clientEnum;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getRequestLimit(): ?int
    {
        return $this->requestLimit;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getMaxOutputToken(): int
    {
        return $this->maxOutputToken;
    }
}