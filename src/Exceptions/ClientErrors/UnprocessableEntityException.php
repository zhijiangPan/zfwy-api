<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions\ClientErrors;

use Zfwy\Api\Exceptions\ClientErrorException;

class UnprocessableEntityException extends ClientErrorException
{
    protected $code = 422;

    protected $message = 'Unprocessable Entity';
}
