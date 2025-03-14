<?php

namespace ArnaudDelgerie\AiToolAgent\Interface;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;

interface ClientConfigInterface
{
    public function getClientEnum(): ClientEnum;

    public function getApiKey(): string;

    public function getModel(): string;

    public function getTemperature(): float;

    public function getRequestLimit(): ?int;

    public function getTimeout(): int;

    public function getMaxOutputToken(): int;
}