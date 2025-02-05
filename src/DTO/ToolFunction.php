<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunctionProperty;

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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    #[Ignore]
    public function getRequiredProperties(): array
    {
        $requiredProperties = [];
        /** @var ToolFunctionProperty $property */
        foreach ($this->properties as $name => $property) {
            if ($property->getRequired()) {
                $requiredProperties[] = $name;
            }
        }

        return $requiredProperties;
    }

    public function addProperty(string $name, ToolFunctionProperty $property): self
    {
        $this->properties[$name] = $property;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[] $properties
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }
}