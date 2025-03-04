<?php

namespace ArnaudDelgerie\AiToolAgent\ToolFunctionManager;

use ArnaudDelgerie\AiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\AiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\AiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\AiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;

class TasksCompletedToolFunctionManager implements ToolFunctionManagerInterface
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

    public function execute(array $args, array $context, array $response): ToolResponse
    {
        $response[self::getName()] = $args['TasksSummary'];

        return new ToolResponse($response, "", true);
    }
}