<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\Util;

use ArnaudDelgerie\SymfonyAiToolAgent\Enum\ClientEnum;

class ClientConfig
{
    public function __construct(
        public ClientEnum $clientEnum,
        public string     $apiKey,
        public string     $model,
        public float      $temperature = 0.5,
        public bool       $toolOnly = true,
        public int        $requestLimit = 10,
        public int        $timeout = 60,
    ) {}
}