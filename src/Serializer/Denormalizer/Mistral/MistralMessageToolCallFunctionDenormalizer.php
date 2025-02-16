<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Denormalizer\Mistral;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCallFunction;

class MistralMessageToolCallFunctionDenormalizer implements DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    public function denormalize(mixed $normalizedToolCallFunction, string $type, ?string $format = null, array $context = []): mixed
    {
        if (isset($normalizedToolCallFunction['arguments']) && is_string($normalizedToolCallFunction['arguments'])) {
            $normalizedToolCallFunction['arguments'] = json_decode($normalizedToolCallFunction['arguments'], true);
        }

        return $this->denormalizer->denormalize($normalizedToolCallFunction, MessageToolCallFunction::class, $format, $context);
    }

    public function supportsDenormalization(mixed $normalizedToolCallFunction, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MessageToolCallFunction::class && $context['clientEnum'] === ClientEnum::Mistral;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MessageToolCallFunction::class => true];
    }
}