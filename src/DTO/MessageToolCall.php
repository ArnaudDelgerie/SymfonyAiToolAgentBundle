<?php

namespace ArnaudDelgerie\AiToolAgent\DTO;

class MessageToolCall
{    
    private ?string $id = null;

    private ?string $type = null;

    private ?MessageToolCallFunction $function = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFunction(): ?MessageToolCallFunction
    {
        return $this->function;
    }

    public function setFunction(MessageToolCallFunction $function): static
    {
        $this->function = $function;

        return $this;
    }
}