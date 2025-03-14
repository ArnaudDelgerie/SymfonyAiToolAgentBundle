<?php

namespace ArnaudDelgerie\AiToolAgent\Exception;

use Exception;

class ClientException extends Exception
{
    public function __construct(string $message = "Client request error", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}