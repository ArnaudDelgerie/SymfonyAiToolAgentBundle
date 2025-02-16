<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

class MessageToolCallFunction
{    
    private ?string $name;

    private ?array $arguments = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function setArguments(?array $arguments): static
    {
        $this->arguments = $arguments;

        return $this;
    }
}