<?php

namespace ArnaudDelgerie\AiToolAgent\Client;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientHelper;
use ArnaudDelgerie\AiToolAgent\Util\ClientResponse;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class MistralClient implements ClientInterface
{
    private int $retry = 0;

    private ?ClientConfigInterface $config = null;

    public function __construct(private ClientHelper $clientHelper) {}

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Mistral;
    }

    public function setConfig(ClientConfigInterface $config): void
    {
        $this->config = $config;
    }

    public function chat(array $messages, array $tools = []): ClientResponse
    {    
        if (!$this->config instanceof ClientConfigInterface) {
            throw new RuntimeException('MistralClient::config must be an instance of ' . ClientConfigInterface::class);
        }


        $client = HttpClient::create(['timeout' => $this->config->getTimeout()]);
        $response = $client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            ],
            'json' => [
                'model' => $this->config->getModel(),
                'temperature' => $this->config->getTemperature(),
                'messages' => $this->clientHelper->normalizeMessages($this->getClientEnum(), $messages),
                'tools' => $this->clientHelper->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => 'any',
            ],
        ]);

        // Use to get around the rate limitations of the free plan
        if (429 === $response->getStatusCode() && $this->retry < 3) {
            sleep(2);
            $this->retry = $this->retry + 1;
            return $this->chat($messages, $tools);
        }

        $this->retry = 0;

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