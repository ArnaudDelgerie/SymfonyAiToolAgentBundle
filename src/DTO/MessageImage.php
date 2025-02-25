<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DTO;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ImageTypeEnum;

class MessageImage
{
    public function __construct(
        public ImageTypeEnum $type,
        public string        $base64
    ) {}
}