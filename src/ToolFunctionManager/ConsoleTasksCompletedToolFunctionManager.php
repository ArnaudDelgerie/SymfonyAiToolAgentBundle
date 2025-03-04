<?php

namespace ArnaudDelgerie\AiToolAgent\ToolFunctionManager;

use ArnaudDelgerie\AiToolAgent\Util\AgentIO;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\Util\ToolValidation;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\AiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class ConsoleTasksCompletedToolFunctionManager implements ConsoleToolFunctionManagerInterface
{
    public static function getName(): string
    {
        return 'tasks_completed';
    }

    public function getToolFunction(array $context): ToolFunction
    {
        return (new ToolFunction())
        ->setName(self::getName())
        ->setDescription('Call this function if all tasks are (already) completed')
        ->addProperty('TasksSummary', (new ToolFunctionProperty())
            ->setType(ToolFunctionPropertyTypeEnum::String)
            ->setDescription('A short summary of the tasks completed')
        );
    }

    public function validate(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolValidation
    {
        return new ToolValidation($args, $responseContent);
    }

    public function execute(array $args, array $context, array $responseContent, AgentIO $agentIO): ToolResponse
    {
        $responseContent[self::getName()] = $args['TasksSummary'];
        $agentIO->text($args['TasksSummary']);

        return new ToolResponse($responseContent, "OK", true);
    }
}