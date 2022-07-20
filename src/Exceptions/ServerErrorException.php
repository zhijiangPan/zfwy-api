<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions;

use Throwable;

class ServerErrorException extends Exception
{
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
