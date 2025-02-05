<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum AiClientEnum: string
{
    case Openai = 'openai';

    case Mistral = 'mistral';
}