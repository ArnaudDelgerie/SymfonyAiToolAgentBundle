<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

class AgentUsageReport
{

    public function __construct(private int $nbRequest = 0, private int $promptTokens = 0, private int $completionTokens = 0) {}

    public function getNbRequest(): int
    {
        return $this->nbRequest;
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->promptTokens + $this->completionTokens;
    }

    public function increaseNbRequest(): static
    {
        $this->nbRequest = $this->nbRequest + 1;

        return $this;
    }

    public function addPromptTokens(int $promptTokens): static
    {
        $this->promptTokens = $this->promptTokens + $promptTokens;

        return $this;
    }

    public function addCompletionToken(int $completionTokens): static
    {
        $this->completionTokens = $this->completionTokens + $completionTokens;

        return $this;
    }

    public function merge(AgentUsageReport $usageReport): static
    {
        $this->nbRequest = $this->nbRequest + $usageReport->getNbRequest();
        $this->promptTokens = $this->promptTokens + $usageReport->getPromptTokens();
        $this->completionTokens = $this->completionTokens + $usageReport->getCompletionTokens();

        return $this;
    }

    public function toArray(): array
    {
        return [
            'nbRequest' => $this->nbRequest,
            'promptTokens' => $this->promptTokens,
            'completionTokens' => $this->completionTokens,
            'totalToken' => $this->promptTokens + $this->completionTokens,
        ];
    }
}