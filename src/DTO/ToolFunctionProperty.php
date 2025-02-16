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

    public function setType(ToolFunctionPropertyTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setEnum(?array $enum): static
    {
        $this->enum = $enum;

        return $this;
    }

    public function getArrayProperty(): ?ToolFunctionProperty
    {
        return $this->arrayProperty;
    }

    public function setArrayProperty(?ToolFunctionProperty $arrayProperty): static
    {
        $this->arrayProperty = $arrayProperty;

        return $this;
    }

    /**
     * @return ?ToolFunctionProperty[]
     */
    public function getObjectProperties(): array
    {
        return $this->objectProperties;
    }

    #[Ignore]
    public function getObjectPropertiesName(): array
    {
        $requiredProperties = [];
        /** @var ToolFunctionProperty $property */
        foreach ($this->objectProperties as $name => $property) {
            $requiredProperties[] = $name;
        }

        return $requiredProperties;
    }

    public function addObjectProperty(string $name, ToolFunctionProperty $property): static
    {
        $this->objectProperties[$name] = $property;

        return $this;
    }

    /**
     * @param ?ToolFunctionProperty[] $objectProperties
     */
    public function setObjectProperties(array $objectProperties): static
    {
        $this->objectProperties = $objectProperties;

        return $this;
    }
}