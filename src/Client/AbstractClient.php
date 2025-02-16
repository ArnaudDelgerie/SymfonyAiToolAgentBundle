<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

readonly abstract class AbstractClient implements ClientInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer
    ) {}

    protected function normalizeMessages(ClientEnum $clientEnum, array $messages): array
    {
        return $this->normalizer->normalize($messages, Message::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    protected function normalizeTools(ClientEnum $clientEnum, array $tools): array
    {
        return $this->normalizer->normalize($tools, ToolFunction::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    protected function denormalizeMessage(ClientEnum $clientEnum, array $message): Message
    {
        return $this->denormalizer->denormalize($message, Message::class, null, [
            'clientEnum' => $clientEnum,
        ]);
    }
}