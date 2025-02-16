<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class OpenaiClient extends AbstractClient
{
    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer) 
    {
        parent::__construct($normalizer, $denormalizer);
    }

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Openai;
    }

    public function chat(string $model,  string $apiKey, array $messages, ?array $tools = [], ?float $temperature = 0.5, bool $onlyTool = true): ClientResponse
    {
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
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
                'tool_choice' => $onlyTool ? 'required' : 'auto'
            ],
        ]);

        try {
            $response = $response->toArray();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            throw new RuntimeException($response->getContent(false));
        }

        $message = $this->denormalizeMessage($this->getClientEnum(), $response['choices'][0]['message']);
        $usageReport = new AgentUsageReport(1, $response['usage']['prompt_tokens'], $response['usage']['completion_tokens']);

        return new ClientResponse($message, $usageReport);
    }
}