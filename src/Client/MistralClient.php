<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\UsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\AiClientEnum;

readonly class MistralClient extends AbstractClient
{
    public function getClientEnum(): AiClientEnum
    {
        return AiClientEnum::Mistral;
    }

    public function chat(string $model, string $apiKey, array $messages, ?array $tools = [], ?float $temperature = 0.5, ?UsageReport &$usageReport = new UsageReport()): Message
    {
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'json' => [
                'model' => $model,
                'temperature' => $temperature,
                'messages' => $this->normalizeMessages($this->getClientEnum(), $messages),
                'tools' => $this->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => 'any'
            ],
        ]);

        // Use to get around the rate limitations of the free plan
        if (429 === $response->getStatusCode()) {
            sleep(2);
            return $this->chat($model, $apiKey, $messages, $tools, $temperature);
        }

        try {
            $response = $response->toArray();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            throw new RuntimeException($response->getContent(false));
        }

        $usageReport
            ->addPromptTokens($response['usage']['prompt_tokens'])
            ->addCompletionToken($response['usage']['completion_tokens']);

        return $this->denormalizeMessage($this->getClientEnum(), $response['choices'][0]['message']);
    }
}