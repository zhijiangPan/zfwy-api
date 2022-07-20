<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions\ClientErrors;

use Zfwy\Api\Exceptions\ClientErrorException;

class UnauthorizedException extends ClientErrorException
{
    protected $code = 401;

    protected $message = 'Unauthorized';
}
