<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Trait;

trait TaskReportTrait
{
    public function updateTaskReport(array $context, string $functionName, array $args): array
    {
        if (!isset($context['taskReport'])) {
            $context['taskReport'] = [];
        }

        if (!isset($context['taskReport'][$functionName])) {
            $context['taskReport'][$functionName] = [];
        }

        $context['taskReport'][$functionName][] = $args;

        return $context;
    }
}