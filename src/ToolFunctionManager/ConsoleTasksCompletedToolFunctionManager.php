<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\ToolFunctionManager;

use ArnaudDelgerie\SymfonyAiToolAgent\Util\AgentIO;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunction;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolResponse;
use ArnaudDelgerie\SymfonyAiToolAgent\Util\ToolValidation;
use ArnaudDelgerie\SymfonyAiToolAgent\Trait\TaskReportTrait;
use ArnaudDelgerie\SymfonyAiToolAgent\DTO\ToolFunctionProperty;
use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ToolFunctionPropertyTypeEnum;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class ConsoleTasksCompletedToolFunctionManager implements ConsoleToolFunctionManagerInterface
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

    public function validate(array &$args, array &$context, array &$taskReport, AgentIO $agentIO): ToolValidation
    {
        return new ToolValidation();
    }

    public function execute(array $args, array &$context, array &$taskReport, AgentIO $agentIO): ToolResponse
    {
        $this->updateTaskReport($taskReport, self::getName(), $args);
        $agentIO->text($args['TasksSummary']);

        return new ToolResponse("OK", true);
    }
}