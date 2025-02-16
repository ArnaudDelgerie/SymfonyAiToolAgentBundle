<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Trait;

trait TaskReportTrait
{
    public function updateTaskReport(array &$taskReport, string $functionName, array $args): void
    {
        if (!isset($taskReport[$functionName])) {
            $taskReport[$functionName] = [];
        }

        $taskReport[$functionName][] = $args;
    }
}