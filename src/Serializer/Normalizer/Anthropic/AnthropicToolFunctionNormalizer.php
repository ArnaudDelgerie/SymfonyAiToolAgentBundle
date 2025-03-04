<?php

namespace ArnaudDelgerie\AiToolAgent\Serializer\Normalizer\Anthropic;

use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AnthropicToolFunctionNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($toolFunction, ?string $format = null, array $context = []): array
    {
        /** @var ToolFunction $toolFunction */
        $normalizedToolFunction = $this->normalizer->normalize($toolFunction, $format, $context);

        $normalizedToolFunction['input_schema'] = [
            'type' => 'object',
            'properties' => $normalizedToolFunction['properties'],
            'required' => $toolFunction->getPropertiesName(),
        ];

        unset($normalizedToolFunction['properties']);

        return $normalizedToolFunction;
    }

    public function supportsNormalization($toolFunction, ?string $format = null, array $context = []): bool
    {
        return $toolFunction instanceof ToolFunction && $context['clientEnum'] === ClientEnum::Anthropic;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ToolFunction::class => true];
    }
}