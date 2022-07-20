<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions\ServerErrors;

use Zfwy\Api\Exceptions\ServerErrorException;

class InternalServerErrorException extends ServerErrorException
{
    protected $code = 500;
}
