<?php

namespace ArnaudDelgerie\AiToolAgent\Serializer\Normalizer\Openai;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OpenaiToolFunctionNormalizer implements NormalizerInterface
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
            'required' => $toolFunction->getPropertiesName(),
        ];

        unset($normalizedToolFunction['properties']);

        return ['type' => 'function', 'function' => $normalizedToolFunction];
    }

    public function supportsNormalization($toolFunction, ?string $format = null, array $context = []): bool
    {
        return $toolFunction instanceof ToolFunction && $context['clientEnum'] === ClientEnum::Openai;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ToolFunction::class => true];
    }
}