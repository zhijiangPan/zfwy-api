<?php

namespace Zfwy\Api;

use ErrorException;
use Zfwy\Api\Http\ResponseFactory;

/**
 * Trait Helpers
 *
 * @property-read \Zfwy\Api\Http\ResponseFactory response
 */
trait Helpers
{
    /**
     * Get the response factory instance.
     *
     * @return \Zfwy\Api\Http\ResponseFactory
     */
    protected function response(): \Zfwy\Api\Http\ResponseFactory
    {
        return app(ResponseFactory::class);
    }

    /**
     * Magically handle calls to certain properties.
     *
     * @param string $key
     *
     * @return mixed
     * @throws \ErrorException
     *
     */
    public function __get(string $key)
    {
        $callable = [
            'response',
        ];

        if (in_array($key, $callable) && method_exists($this, $key)) {
            return $this->$key();
        }

        throw new ErrorException('Undefined property ' . get_class($this) . '::' . $key);
    }

    /**
     * Magically handle calls to certain methods on the response factory.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return \Zfwy\Api\Http\ResponseFactory
     * @throws \ErrorException
     *
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->response(), $method) || $method == 'array') {
            return call_user_func_array([$this->response(), $method], $parameters);
        }

        throw new ErrorException('Undefined method ' . get_class($this) . '::' . $method);
    }
}
