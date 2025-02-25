<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

use RuntimeException;
use Symfony\Component\Serializer\Annotation\Ignore;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use Symfony\Component\Serializer\Attribute\SerializedName;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;

class Message
{
    private ?MessageRoleEnum $role = null;
    
    private ?string $content = null;

    private ?string $name = null;

    #[SerializedName('tool_call_id')]
    private ?string $toolCallId = null;

    #[SerializedName('tool_calls')]
    private ?array $toolCalls = null;

    private array $images = [];

    public function getRole(): ?MessageRoleEnum
    {
        return $this->role;
    }

    public function setRole(MessageRoleEnum $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getToolCallId(): ?string
    {
        return $this->toolCallId;
    }

    public function setToolCallId(?string $toolCallId): static
    {
        $this->toolCallId = $toolCallId;

        return $this;
    }

    public function getToolCalls(): ?array
    {
        return $this->toolCalls;
    }

    /**
     * @param ?MessageToolCall[] $toolCalls
     */
    public function setToolCalls(?array $toolCalls): static
    {
        $this->toolCalls = $toolCalls;

        return $this;
    }

    #[Ignore]
    /**
     * @return ?MessageImage[]
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @param ?MessageImage[] $images
     */
    public function setImages(?array $images): static
    {
        foreach ($images as $image) {
            if (!$image instanceof MessageImage) {
                throw new RuntimeException('$images must be an array of ' . MessageImage::class);
            }
        }

        $this->images = $images;

        return $this;
    }
}