<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Client;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\Message;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientConfig;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientHelper;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ClientResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class AnthropicClient implements ClientInterface
{
    private ?ClientConfig $config = null;

    public function __construct(private ClientHelper $clientHelper) {}

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Anthropic;
    }

    public function setConfig(ClientConfig $config): void
    {
        $this->config = $config;
    }

    public function chat(array  $messages, array  $tools = []): ClientResponse
    {
        if (!$this->config instanceof ClientConfig) {
            throw new RuntimeException('AnthropicClient::config must be an instance of ClientCongig');
        }

        /** @var Message $sysMessage */
        $sysMessage = array_shift($messages);

        $client = HttpClient::create(['timeout' => $this->config->timeout]);
        $response = $client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $this->config->apiKey,
            ],
            'json' => [
                'model' => $this->config->model,
                'temperature' => $this->config->temperature,
                'max_tokens' => 8192,
                'system' => $sysMessage->getContent(),
                'messages' => $this->clientHelper->normalizeMessages($this->getClientEnum(), $messages),
                'tools' => $this->clientHelper->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => ['type' => $this->config->toolOnly ? 'any' : 'auto', 'disable_parallel_tool_use' => false]
            ],
        ]);

        try {
            $response = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw $e;
        }

        $message = $this->clientHelper->denormalizeMessage($this->getClientEnum(), ['role' => $response['role'], 'content' => $response['content']]);
        $usageReport = new AgentUsageReport(1, $response['usage']['input_tokens'], $response['usage']['output_tokens']);

        return new ClientResponse($message, $usageReport);
    }
}