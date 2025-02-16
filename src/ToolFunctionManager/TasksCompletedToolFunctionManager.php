<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\ToolFunctionManager;

use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\Trait\TaskReportTrait;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;

class TasksCompletedToolFunctionManager implements ToolFunctionManagerInterface
{
    use TaskReportTrait;

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

    public function execute(array $args, array &$context, array &$taskReport): ToolResponse
    {
        $this->updateTaskReport($taskReport, self::getName(), $args);

        return new ToolResponse("OK", true);
    }
}