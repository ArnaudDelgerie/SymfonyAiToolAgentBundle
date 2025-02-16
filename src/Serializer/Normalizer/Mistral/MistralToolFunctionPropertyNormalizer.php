<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Mistral;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunctionProperty;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ToolFunctionPropertyTypeEnum;

class MistralToolFunctionPropertyNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($toolFunctionProperty, ?string $format = null, array $context = []): array
    {
        /** @var ToolFunctionProperty $toolFunctionProperty */
        $normalizedToolFunctionProperty = $this->normalizer->normalize($toolFunctionProperty, $format, $context);

        if ($toolFunctionProperty->getType() === ToolFunctionPropertyTypeEnum::Object) {
            $normalizedToolFunctionProperty['additionalProperties'] = false;
            $normalizedToolFunctionProperty['required'] = $toolFunctionProperty->getObjectPropertiesName();
        }

        return $normalizedToolFunctionProperty;
    }

    public function supportsNormalization($toolFunctionProperty, ?string $format = null, array $context = []): bool
    {
        return $toolFunctionProperty instanceof ToolFunctionProperty && $context['clientEnum'] === ClientEnum::Mistral;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ToolFunctionProperty::class => true];
    }
}