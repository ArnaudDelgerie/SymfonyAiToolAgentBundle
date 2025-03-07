<?php

namespace ArnaudDelgerie\AiToolAgent\Interface;

interface AgentConfigInterface
{
    public function getSystemPrompt(): string;

    public function getFunctionNames(): array;

    public function getContext(): array;
}