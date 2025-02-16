<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use ArnaudDelgerie\SymfonyAiToolAgent\Agent\ToolAgent;
use ArnaudDelgerie\SymfonyAiToolAgent\Agent\ConsoleToolAgent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ClientResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Resolver\ToolFunctionResolver;

class ToolAgentProvider
{
    public function __construct(
        private ClientResolver       $clientResolver,
        private ValidatorInterface   $validator,
        private ToolFunctionResolver $toolFunctionResolver,
        private NormalizerInterface  $normalizer,
    ) {}

    public function createToolAgent(): ToolAgent
    {
        return new ToolAgent($this->clientResolver, $this->validator, $this->toolFunctionResolver, $this->normalizer);
    }

    public function createConsoleToolAgent(): ConsoleToolAgent
    {
        return new ConsoleToolAgent($this->clientResolver, $this->validator, $this->toolFunctionResolver, $this->normalizer);
    }
}