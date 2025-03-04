<?php

namespace ArnaudDelgerie\AiToolAgent\Enum;

enum ImageTypeEnum: string
{
    case JPEG = 'image/jpeg';

    case PNG = 'image/png';

    case WEBP = 'image/webp';
}