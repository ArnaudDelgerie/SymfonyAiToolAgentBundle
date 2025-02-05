<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum MessageRoleEnum: string
{
    case System = 'system';

    case User = 'user';
    
    case Assistant = 'assistant';

    case Tool = 'tool';
}