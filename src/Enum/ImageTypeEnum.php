<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Enum;

enum ImageTypeEnum: string
{
    case JPEG = 'image/jpeg';

    case PNG = 'image/png';

    case WEBP = 'image/webp';
}