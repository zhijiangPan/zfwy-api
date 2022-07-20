<?php

declare(strict_types = 1);

namespace Zfwy\Api\Exceptions;

use Throwable;
use Illuminate\Support\Arr;
use Zfwy\Api\Http\Response;

trait Helpers
{
    /**
     * 转换 render to api response
     *
     * @param            $request
     * @param \Throwable $exception
     * @param string     $handleCorsClass
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function covertRenderToApiResponse($request, Throwable $exception, $handleCorsClass = null)
    {
        try {
            parent::render($request, $exception);
        } catch (\Throwable $throwable) {
            $exception = $throwable;
        }

        return $this->covertExceptionToApiResponse($request, $exception, $handleCorsClass);
    }

    /**
     * 转换异常为 api response
     *
     * @param            $request
     * @param \Throwable $exception
     * @param string     $handleCorsClass
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function covertExceptionToApiResponse($request, Throwable $exception, $handleCorsClass = null)
    {
        $response = (new Response(null, $this->covertExceptionCode($exception)))
            ->setMessage($this->covertExceptionMessage($exception))
            ->withException($exception)->prepare($request);

        if ($handleCorsClass) {
            $response = tap($response, function () use ($request, $response, $handleCorsClass) {
                app($handleCorsClass)->handle($request, function ($request) use ($response) {
                    return $response;
                });
            });
        }

        return $response;
    }

    protected function covertExceptionCode($exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $code = $exception->status;
        } elseif (
            $exception instanceof \Illuminate\Database\QueryException
            || $exception instanceof \Illuminate\Http\Client\ConnectionException
        ) {
            $code = (new ServerErrorException)->getCode();
        } elseif ($this->isHttpException($exception)) {
            $code = $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }

        if (!$code || !is_int($code)) {
            $code = 500;
        }

        return $code;
    }

    protected function covertExceptionMessage($exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $message = Arr::first(Arr::first($exception->errors()));
        } elseif (
            $exception instanceof \Illuminate\Database\QueryException
            || $exception instanceof \Illuminate\Http\Client\ConnectionException
        ) {
            $message = (new ServerErrorException)->getMessage();
        } else {
            $message = $exception->getMessage();
        }

        return $message;
    }
}
