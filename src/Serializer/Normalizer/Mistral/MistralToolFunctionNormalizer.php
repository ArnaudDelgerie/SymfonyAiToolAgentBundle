<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Mistral;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MistralToolFunctionNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($toolFunction, ?string $format = null, array $context = []): array
    {
        /** @var ToolFunction $toolFunction */
        $normalizedToolFunction = $this->normalizer->normalize($toolFunction, $format, $context);

        $normalizedToolFunction['strict'] = true;
        $normalizedToolFunction['parameters'] = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => $normalizedToolFunction['properties'],
            'required' => $toolFunction->getPropertiesName()
        ];

        unset($normalizedToolFunction['properties']);

        return ['type' => 'function', 'function' => $normalizedToolFunction];
    }

    public function supportsNormalization($toolFunction, ?string $format = null, array $context = []): bool
    {
        return $toolFunction instanceof ToolFunction && $context['clientEnum'] === ClientEnum::Mistral;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ToolFunction::class => true];
    }
}