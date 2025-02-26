<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Serializer\Normalizer\Anthropic;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageImage;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\MessageToolCall;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\MessageRoleEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AnthropicMessageNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize($message, ?string $format = null, array $context = []): array
    {
        /** @var Message $message */
        $normalizedMessage = $messageContents = [];
        if ($message->getRole() === MessageRoleEnum::Assistant) {
            $normalizedMessage['role'] = 'assistant';
            if ($message->getContent() !== null && $message->getContent() !== "") {
                $messageContents[] = [
                    'type' => 'text',
                    'text' => $message->getContent(),
                ];
            }

            /** @var MessageToolCall $toolCall */
            foreach ($message->getToolCalls() ?? [] as $toolCall) {
                $messageContents[] = [
                    'type' => 'tool_use',
                    'id' => $toolCall->getId(),
                    'name' => $toolCall->getFunction()->getName(),
                    'input' => $toolCall->getFunction()->getArguments(),
                ];
            }

            $normalizedMessage['content'] = $messageContents;
        } elseif ($message->getRole() === MessageRoleEnum::Tool) {
            $normalizedMessage['role'] = 'user';
            $messageContents[] = [
                'type' => 'tool_result',
                'tool_use_id' => $message->getToolCallId(),
                'content' => $message->getContent(),
            ];
        } else {
            $normalizedMessage['role'] = 'user';
            if ($message->getContent() !== null && $message->getContent() !== "") {
                $messageContents[] = [
                    'type' => 'text',
                    'text' => $message->getContent(),
                ];
            }
            /** @var MessageImage $image */
            foreach ($message->getImages() as $image) {
                $messageContents[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $image->type->value,
                        'data' => $image->base64
                    ]
                ];
            }
        }

        $normalizedMessage['content'] = $messageContents;

        return $normalizedMessage;
    }

    public function supportsNormalization($message, ?string $format = null, array $context = []): bool
    {
        return $message instanceof Message && $context['clientEnum'] === ClientEnum::Anthropic;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}
