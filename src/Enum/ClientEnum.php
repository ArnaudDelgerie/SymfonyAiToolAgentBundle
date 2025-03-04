<?php

namespace ArnaudDelgerie\AiToolAgent\Enum;

enum ClientEnum: string
{
    case Openai = 'openai';

    case Mistral = 'mistral';

    case Anthropic = 'anthropic';
}