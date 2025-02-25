<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientHelper;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class OpenaiClient implements ClientInterface
{
    private ?ClientConfig $config = null;

    public function __construct(private ClientHelper $clientHelper) {}

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Openai;
    }

    public function setConfig(ClientConfig $config): void
    {
        $this->config = $config;
    }

    public function chat(array  $messages, array  $tools = []): ClientResponse
    {
        if (!$this->config instanceof ClientConfig) {
            throw new RuntimeException('OpenaiClient::config must be an instance of ClientCongig');
        }

        $client = HttpClient::create(['timeout' => $this->config->timeout]);
        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->apiKey,
            ],
            'json' => [
                'model' => $this->config->model,
                'temperature' => $this->config->temperature,
                'messages' => $this->clientHelper->normalizeMessages($this->getClientEnum(), $messages),
                'tools' => $this->clientHelper->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => $this->config->toolOnly ? 'required' : 'auto'
            ],
        ]);

        try {
            $response = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw $e;
        }

        $message = $this->clientHelper->denormalizeMessage($this->getClientEnum(), $response['choices'][0]['message']);
        $usageReport = new AgentUsageReport(1, $response['usage']['prompt_tokens'], $response['usage']['completion_tokens']);

        return new ClientResponse($message, $usageReport);
    }
}