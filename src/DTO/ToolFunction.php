<?php

namespace ArnaudDelgerie\AiToolAgent\DTO;

use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;

class ToolFunction
{
    #[Assert\NotBlank]
    private ?string $name = null;
    
    #[Assert\NotBlank]
    private ?string $description = null;

    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    private array $properties = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return ?ToolFunctionProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    #[Ignore]
    public function getPropertiesName(): array
    {
        $requiredProperties = [];
        /** @var ToolFunctionProperty $property */
        foreach ($this->properties as $name => $property) {
            $requiredProperties[] = $name;
        }

        return $requiredProperties;
    }

    public function addProperty(string $name, ToolFunctionProperty $property): static
    {
        $this->properties[$name] = $property;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[] $properties
     */
    public function setProperties(array $properties): static
    {
        $this->properties = $properties;

        return $this;
    }
}