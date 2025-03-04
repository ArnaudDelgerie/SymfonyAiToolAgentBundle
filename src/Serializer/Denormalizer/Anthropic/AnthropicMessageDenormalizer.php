<?php

namespace ArnaudDelgerie\AiToolAgent\Serializer\Denormalizer\Anthropic;

use ArnaudDelgerie\AiToolAgent\DTO\Message;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AnthropicMessageDenormalizer implements DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    public function denormalize(mixed $normalizedMessage, string $type, ?string $format = null, array $context = []): mixed
    {
        if (is_array($normalizedMessage['content'])) {
            $contents = $normalizedMessage['content'];
            $toolCalls = [];
            $textContent = null;
            foreach ($contents as $content) {
                if (isset($content['type']) && 'text' === $content['type']) {
                    $textContent = $content['text'];
                }
                if (isset($content['type']) && 'tool_use' === $content['type']) {
                    $toolCalls[] = [
                        'id' => $content['id'],
                        'type' => 'function',
                        'function' => [
                            'name' => $content['name'],
                            'arguments' => $content['input'],
                        ]

                    ];
                }
            }

            $normalizedMessage['content'] = $textContent;
            $normalizedMessage['tool_calls'] = $toolCalls;
        }

        return $this->denormalizer->denormalize($normalizedMessage, Message::class, $format, $context);
    }

    public function supportsDenormalization(mixed $normalizedMessage, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Message::class && $context['clientEnum'] === ClientEnum::Anthropic;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Message::class => true];
    }
}
