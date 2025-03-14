<?php

namespace ArnaudDelgerie\AiToolAgent\Util\Config\Abstract;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;

abstract class AbstractClientConfig implements ClientConfigInterface
{
    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Mistral;
    }

    public function getApiKey(): string
    {
        return '';
    }

    public function getModel(): string
    {
        return 'mistral-large-latest';
    }

    public function getTemperature(): float
    {
        return 1.0;
    }

    public function getRequestLimit(): ?int
    {
        return null;
    }

    public function getTimeout(): int
    {
        return 60;
    }

    public function getMaxOutputToken(): int
    {
        return 8192;
    }
}