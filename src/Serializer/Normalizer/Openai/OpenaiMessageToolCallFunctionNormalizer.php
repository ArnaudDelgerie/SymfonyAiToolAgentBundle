<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Openai;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCallFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OpenaiMessageToolCallFunctionNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($toolCallFunction, ?string $format = null, array $context = []): array
    {
        $normalizedToolCallFunction = $this->normalizer->normalize($toolCallFunction, MessageToolCallFunction::class, $context);

        if (isset($normalizedToolCallFunction['arguments']) && is_array($normalizedToolCallFunction['arguments'])) {
            $normalizedToolCallFunction['arguments'] = json_encode($normalizedToolCallFunction['arguments']);
        }

        return $normalizedToolCallFunction;
    }

    public function supportsNormalization($toolCallFunction, ?string $format = null, array $context = []): bool
    {
        return $toolCallFunction instanceof MessageToolCallFunction && $context['clientEnum'] === ClientEnum::Openai;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MessageToolCallFunction::class => true];
    }
}