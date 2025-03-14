<?php

namespace ArnaudDelgerie\AiToolAgent\Client;

use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientHelper;
use ArnaudDelgerie\AiToolAgent\Util\ClientResponse;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Exception\ClientException;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class OpenaiClient implements ClientInterface
{
    private ?ClientConfigInterface $config = null;

    public function __construct(private ClientHelper $clientHelper) {}

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Openai;
    }

    public function setConfig(ClientConfigInterface $config): void
    {
        $this->config = $config;
    }

    public function chat(array  $messages, array  $tools = []): ClientResponse
    {
        $client = HttpClient::create(['timeout' => $this->config->getTimeout()]);
        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            ],
            'json' => [
                'model' => $this->config->getModel(),
                'temperature' => $this->config->getTemperature(),
                'max_completion_tokens' => $this->config->getMaxOutputToken(),
                'messages' => $this->clientHelper->normalizeMessages($this->getClientEnum(), $messages),
                'tools' => $this->clientHelper->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => 'required',
            ],
        ]);

        try {
            $response = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($response->getContent(false), $response->getStatusCode());
        }

        $message = $this->clientHelper->denormalizeMessage($this->getClientEnum(), $response['choices'][0]['message']);
        $usageReport = new AgentUsageReport(1, $response['usage']['prompt_tokens'], $response['usage']['completion_tokens']);

        return new ClientResponse($message, $usageReport);
    }
}