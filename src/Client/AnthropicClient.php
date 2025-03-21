<?php

namespace ArnaudDelgerie\AiToolAgent\Client;

use ArnaudDelgerie\AiToolAgent\DTO\Message;
use Symfony\Component\HttpClient\HttpClient;
use ArnaudDelgerie\AiToolAgent\Enum\ClientEnum;
use ArnaudDelgerie\AiToolAgent\Util\ClientHelper;
use ArnaudDelgerie\AiToolAgent\Util\ClientResponse;
use ArnaudDelgerie\AiToolAgent\Util\AgentUsageReport;
use ArnaudDelgerie\AiToolAgent\Exception\ClientException;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ClientConfigInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class AnthropicClient implements ClientInterface
{
    private ?ClientConfigInterface $config = null;

    public function __construct(private ClientHelper $clientHelper) {}

    public function getClientEnum(): ClientEnum
    {
        return ClientEnum::Anthropic;
    }

    public function setConfig(ClientConfigInterface $config): void
    {
        $this->config = $config;
    }

    public function chat(array  $messages, array  $tools = []): ClientResponse
    {
        /** @var Message $sysMessage */
        $sysMessage = array_shift($messages);

        $client = HttpClient::create(['timeout' => $this->config->getTimeout()]);
        $response = $client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $this->config->getApiKey(),
            ],
            'json' => [
                'model' => $this->config->getModel(),
                'temperature' => $this->config->getTemperature(),
                'max_tokens' => $this->config->getMaxOutputToken(),
                'system' => $sysMessage->getContent(),
                'messages' => $this->mergeUserMessage($this->clientHelper->normalizeMessages($this->getClientEnum(), $messages)),
                'tools' => $this->clientHelper->normalizeTools($this->getClientEnum(), $tools),
                'tool_choice' => ['type' => 'any', 'disable_parallel_tool_use' => false],
            ],
        ]);

        try {
            $response = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($response->getContent(false), $response->getStatusCode());
        }

        $message = $this->clientHelper->denormalizeMessage($this->getClientEnum(), ['role' => $response['role'], 'content' => $response['content']]);
        $usageReport = new AgentUsageReport(1, $response['usage']['input_tokens'], $response['usage']['output_tokens']);

        return new ClientResponse($message, $usageReport);
    }

    private function mergeUserMessage(array $messages): array
    {
        $mergedMessages = [];
    
        foreach ($messages as $message) {
            $lastIndex = count($mergedMessages) - 1;
    
            if ($lastIndex >= 0 && $message['role'] === 'user' && $mergedMessages[$lastIndex]['role'] === 'user') {
                $mergedMessages[$lastIndex]['content'] = array_merge(
                    $mergedMessages[$lastIndex]['content'],
                    $message['content']
                );
            } else {
                $mergedMessages[] = $message;
            }
        }
    
        return $mergedMessages;
    }
}