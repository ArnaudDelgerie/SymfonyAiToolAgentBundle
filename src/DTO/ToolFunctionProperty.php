<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\SerializedName;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ToolFunctionPropertyTypeEnum;

class ToolFunctionProperty
{
    #[Assert\NotBlank]
    private ?ToolFunctionPropertyTypeEnum $type = null;

    private ?string $description = null;

    private bool $required = true;

    private ?array $enum = null;

    #[SerializedName('items')]
    #[Assert\When(
        expression: 'this.getType().value == "array"',
        constraints: [new Assert\NotBlank(message: 'This value should not be blank when "type" === "array"')],
    )]
    private ?ToolFunctionProperty $arrayProperty = null;

    #[SerializedName('properties')]
    #[Assert\When(
        expression: 'this.getType().value == "object"',
        constraints: [new Assert\NotBlank(message: 'This value should not be blank when "type" === "object"')],
    )]
    private ?array $objectProperties = null;

    public function getType(): ?ToolFunctionPropertyTypeEnum
    {
        return $this->type;
    }

    public function setType(ToolFunctionPropertyTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Ignore]
    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setEnum(?array $enum): self
    {
        $this->enum = $enum;

        return $this;
    }

    public function getArrayProperty(): ?ToolFunctionProperty
    {
        return $this->arrayProperty;
    }

    public function setArrayProperty(?ToolFunctionProperty $arrayProperty): self
    {
        $this->arrayProperty = $arrayProperty;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[]
     */
    public function getObjectProperties(): array
    {
        return $this->objectProperties;
    }

    #[Ignore]
    public function getObjectRequiredProperties(): array
    {
        $requiredProperties = [];
        /** @var ToolFunctionProperty $property */
        foreach ($this->objectProperties as $name => $property) {
            if ($property->getRequired()) {
                $requiredProperties[] = $name;
            }
        }

        return $requiredProperties;
    }

    public function addObjectProperty(string $name, ToolFunctionProperty $property): self
    {
        $this->objectProperties[$name] = $property;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[] $objectProperties
     */
    public function setObjectProperties(array $objectProperties): self
    {
        $this->objectProperties = $objectProperties;

        return $this;
    }
}