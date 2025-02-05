<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum ToolFunctionPropertyTypeEnum: string
{
    case String = 'string';

    case Number = 'number';

    case Boolean = 'boolean';

    case Array = 'array';

    case Object = 'object';
}