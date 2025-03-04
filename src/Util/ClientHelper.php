<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ClientHelper
{
    public function __construct(
        private NormalizerInterface   $normalizer,
        private DenormalizerInterface $denormalizer
    ) {}

    public function normalizeMessages(ClientEnum $clientEnum, array $messages): array
    {
        return $this->normalizer->normalize($messages, Message::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    public function normalizeTools(ClientEnum $clientEnum, array $tools): array
    {
        return $this->normalizer->normalize($tools, ToolFunction::class, [
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'clientEnum' => $clientEnum,
        ]);
    }

    public function denormalizeMessage(ClientEnum $clientEnum, array $message): Message
    {
        return $this->denormalizer->denormalize($message, Message::class, null, [
            'clientEnum' => $clientEnum,
        ]);
    }
}