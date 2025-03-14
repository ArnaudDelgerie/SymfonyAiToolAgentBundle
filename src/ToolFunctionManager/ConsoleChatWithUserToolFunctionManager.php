<?php

namespace ArnaudDelgerie\AiToolAgent\ToolFunctionManager;

use ArnaudDelgerie\AiToolAgent\Util\AgentIO;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\Util\ToolValidation;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\AiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class ConsoleChatWithUserToolFunctionManager implements ConsoleToolFunctionManagerInterface
{
    public static function getName(): string
    {
        return 'chat_with_user';
    }

    public function getToolFunction(array $context): ToolFunction
    {
        return (new ToolFunction())
            ->setName(self::getName())
            ->setDescription('Use this function to send message to user (to summarize the tasks you have just performed or to ask a question)')
            ->addProperty(
                'message',
                (new ToolFunctionProperty())
                    ->setType(ToolFunctionPropertyTypeEnum::String)
                    ->setDescription('summary or question for the user')
            );
    }

    public function validate(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolValidation
    {
        return new ToolValidation($args, $responseContent, true);
    }

    public function execute(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolResponse
    {   
        $userPrompt = $agentIO->ask($args['message'], 'stop');

        return new ToolResponse($responseContent, $userPrompt, $userPrompt === 'stop');
    }
}
