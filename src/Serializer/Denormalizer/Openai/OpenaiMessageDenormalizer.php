<?php

namespace ArnaudDelgerie\AiToolAgent\Serializer\Denormalizer\Openai;

use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class OpenaiMessageDenormalizer implements DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    public function denormalize(mixed $normalizedMessage, string $type, ?string $format = null, array $context = []): mixed
    {
        if ($normalizedMessage['role'] === 'developer') {
            $normalizedMessage['role'] = 'system';
        }

        return $this->denormalizer->denormalize($normalizedMessage, Message::class, $format, $context);
    }

    public function supportsDenormalization(mixed $normalizedMessage, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Message::class && $context['clientEnum'] === ClientEnum::Openai;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}