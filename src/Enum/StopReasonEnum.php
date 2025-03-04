<?php

namespace ArnaudDelgerie\AiToolAgent\Enum;

enum StopReasonEnum
{
    case Function;

    case Command;

    case RequestLimit;
}