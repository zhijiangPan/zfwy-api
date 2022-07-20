<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions\ClientErrors;

use Zfwy\Api\Exceptions\ClientErrorException;

class ForbiddenException extends ClientErrorException
{
    protected $code = 403;

    protected $message = 'Forbidden';
}
