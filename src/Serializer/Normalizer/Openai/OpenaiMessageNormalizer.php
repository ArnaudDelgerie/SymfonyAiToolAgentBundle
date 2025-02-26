<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Openai;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageImage;
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

        $images = $message->getImages();
        if (count($images) > 0) {
            $messageContents = [];
            if ($message->getContent() !== null && $message->getContent() !== "") {
                $messageContents[] = ['type' => 'text', 'text' => $normalizedMessage['content']];
            }

            /** @var MessageImage $image */
            foreach ($images as $image) {
                $messageContents[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => \sprintf("data:%s;base64,%s", $image->type->value, $image->base64)],
                ];
            }

            $normalizedMessage['content'] = $messageContents;
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