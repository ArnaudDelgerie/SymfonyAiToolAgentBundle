<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum ClientEnum: string
{
    case Openai = 'openai';

    case Mistral = 'mistral';
}