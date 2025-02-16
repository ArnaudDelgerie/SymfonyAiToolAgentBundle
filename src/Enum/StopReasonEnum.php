<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum StopReasonEnum
{
    case Function;

    case Command;

    case RequestLimit;
}