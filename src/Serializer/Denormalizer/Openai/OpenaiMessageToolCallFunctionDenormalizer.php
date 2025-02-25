<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Denormalizer\Openai;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCallFunction;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OpenaiMessageToolCallFunctionDenormalizer implements DenormalizerInterface
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
        return $type === MessageToolCallFunction::class && $context['clientEnum'] === ClientEnum::Openai;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MessageToolCallFunction::class => true];
    }
}