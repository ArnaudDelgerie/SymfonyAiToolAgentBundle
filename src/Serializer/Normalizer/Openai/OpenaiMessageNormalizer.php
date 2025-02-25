<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Openai;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OpenaiMessageNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($message, ?string $format = null, array $context = []): array
    {
        /** @var Message $message */
        $normalizedMessage = $this->normalizer->normalize($message, $format, $context);

        if ($normalizedMessage['role'] === 'system') {
            $normalizedMessage['role'] = 'developer';
        }

        return $normalizedMessage;
    }

    public function supportsNormalization($message, ?string $format = null, array $context = []): bool
    {
        return $message instanceof Message && $context['clientEnum'] === ClientEnum::Openai;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}