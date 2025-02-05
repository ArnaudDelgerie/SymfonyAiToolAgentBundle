<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

class MessageToolCall
{    
    private ?string $id = null;

    private ?string $type = null;

    private ?MessageToolCallFunction $function = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFunction(): ?MessageToolCallFunction
    {
        return $this->function;
    }

    public function setFunction(MessageToolCallFunction $function): self
    {
        $this->function = $function;

        return $this;
    }
}