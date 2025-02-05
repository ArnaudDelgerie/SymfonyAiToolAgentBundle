<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

class UsageReport
{
    private int $promptTokens;

    private int $completionTokens;

    public function __construct(?int $promptTokens = 0, ?int $completionTokens = 0) 
    {
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
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

    public function addPromptTokens(int $promptTokens): self
    {
        $this->promptTokens = $this->promptTokens + $promptTokens;

        return $this;
    }

    public function addCompletionToken(int $completionTokens): self
    {
        $this->completionTokens = $this->completionTokens + $completionTokens;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'promptTokens' => $this->promptTokens,
            'completionTokens' => $this->completionTokens,
            'totalToken' => $this->promptTokens + $this->completionTokens,
        ];
    }
}