<?php

namespace ArnaudDelgerie\AiToolAgent\Enum;

enum ToolFunctionPropertyTypeEnum: string
{
    case String = 'string';

    case Number = 'number';

    case Boolean = 'boolean';

    case Array = 'array';

    case Object = 'object';
}