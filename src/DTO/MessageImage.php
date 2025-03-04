<?php

namespace ArnaudDelgerie\AiToolAgent\DTO;

use ArnaudDelgerie\AiToolAgent\Enum\ImageTypeEnum;

class MessageImage
{
    public function __construct(
        public ImageTypeEnum $type,
        public string        $base64
    ) {}
}