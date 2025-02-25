<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Mistral;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageImage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MistralMessageNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($message, ?string $format = null, array $context = []): array
    {
        /** @var Message $message */
        $normalizedMessage = $this->normalizer->normalize($message, $format, $context);

        if (count($message->getImages()) > 0) {
            $messageContents = [];
            if ($normalizedMessage['content'] !== "") {
                $messageContents[] = ['type' => 'text', 'text' => $normalizedMessage['content']];
            }

            /** @var MessageImage $image */
            foreach ($message->getImages() as $image) {
                $messageContents[] = [
                    'type' => 'image_url',
                    'image_url' => \sprintf("data:%s;base64,%s", $image->type->value, $image->base64),
                ];
            }

            $normalizedMessage['content'] = $messageContents;
        }

        return $normalizedMessage;
    }

    public function supportsNormalization($message, ?string $format = null, array $context = []): bool
    {
        return $message instanceof Message && $context['clientEnum'] === ClientEnum::Mistral;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}