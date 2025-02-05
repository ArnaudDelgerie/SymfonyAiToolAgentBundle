<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\AiClientEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\AiClientInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

readonly abstract class AbstractClient implements AiClientInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer
    ) {}

    protected function normalizeMessages(AiClientEnum $clientEnum, array $messages): array
    {
        return $this->normalizer->normalize($messages, Message::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    protected function normalizeTools(AiClientEnum $clientEnum, array $tools): array
    {
        return $this->normalizer->normalize($tools, ToolFunction::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    protected function denormalizeMessage(AiClientEnum $clientEnum, array $message): Message
    {
        return $this->denormalizer->denormalize($message, Message::class, null, [
            'clientEnum' => $clientEnum,
        ]);
    }
}