<?php

declare(strict_types = 1);

namespace Zfwy\Api\Http;

use ErrorException;
use Illuminate\Support\Str;
use Zfwy\Api\Exceptions\ClientErrorException;
use Zfwy\Api\Exceptions\ServerErrorException;

/**
 * @method \Zfwy\Api\Http\Response array(array $args)
 */
class ResponseFactory
{
    /**
     * 成功
     *
     * @param string|null $message
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function successful($message = 'Successful')
    {
        return (new Response(null, 200))->setMessage($message);
    }

    /**
     * Respond with a created response and associate a location if provided.
     *
     * @param array|null  $content
     * @param string $message
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function created($content = null, $message = 'Created')
    {
        return (new Response($content, 201))->setMessage($message);
    }

    /**
     * Respond with an accepted response and associate a location and/or content if provided.
     *
     * @param array|null  $content
     * @param string $message
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function accepted($content = null, $message = 'Accepted')
    {
        return (new Response($content, 202))->setMessage($message);
    }

    /**
     * Respond with a no content response.
     *
     * @param string|null $message
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function noContent($message = null)
    {
         return (new Response(null, 204))->setMessage($message);
    }

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param $collection
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function collection($collection)
    {
        return (new Response($collection))->withList();
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object $item
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function item($resource)
    {
        return new Response($resource);
    }

    /**
     * Return an client error response.
     *
     * @param string $message
     * @param int    $statusCode
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function clientError($message, $statusCode)
    {
        throw new ClientErrorException($message, $statusCode);
    }

    /**
     * Return an server error response.
     *
     * @param string $message
     * @param int    $statusCode
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ServerErrorException
     *
     */
    public function serverError($message, $statusCode)
    {
        throw new ServerErrorException($message, $statusCode);
    }

    /**
     * Return a 400 bad request error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function errorBadRequest($message = 'Bad Request')
    {
        $this->clientError($message, 400);
    }

    /**
     * Return a 401 unauthorized error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        $this->clientError($message, 401);
    }

    /**
     * Return a 403 forbidden error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function errorForbidden($message = 'Forbidden')
    {
        $this->clientError($message, 403);
    }

    /**
     * Return a 404 not found error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function errorNotFound($message = 'Not Found')
    {
        $this->clientError($message, 404);
    }

    /**
     * Return a 405 method not allowed error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *
     */
    public function errorMethodNotAllowed($message = 'Method Not Allowed')
    {
        $this->clientError($message, 405);
    }

    /**
     * 表单验证错误
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ClientErrorException
     *                                                           */
    public function errorUnprocessableEntity($message = 'Unprocessable Entity')
    {
        $this->clientError($message, 422);
    }

    /**
     * Return a 500 internal server error.
     *
     * @param string $message
     *
     * @return void
     * @throws \Zfwy\Api\Exceptions\ServerErrorException
     *
     */
    public function errorInternal($message = 'Internal Error')
    {
        $this->serverError($message, 500);
    }

    /**
     * Call magic methods beginning with "with".
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ErrorException
     *
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return call_user_func_array([$this, Str::camel(substr($method, 4))], $parameters);

            // Because PHP won't let us name the method "array" we'll simply watch for it
            // in here and return the new binding. Gross. This is now DEPRECATED and
            // should not be used. Just return an array or a new response instance.
        } elseif ($method == 'array') {
            return new Response($parameters[0]);
        }

        throw new ErrorException('Undefined method ' . get_class($this) . '::' . $method);
    }
}
