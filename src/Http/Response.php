<?php

declare(strict_types = 1);

namespace Zfwy\Api\Http;

use Illuminate\Support\Arr;
use UnexpectedValueException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse
{
    /**
     * 自定义 code
     *
     * @var int
     */
    protected $code;

    /**
     * 接口返回的 message
     *
     * @var string
     */
    protected $message;

    /**
     * HTTP Status Code
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * 已装换 content
     * 防止多次执行转换
     *
     * @var bool
     */
    protected $transformedContent = false;

    /**
     * 响应数据 key to camel 小写驼峰
     *
     * @var bool
     */
    protected $contentKeysToCamel = false;

    /**
     * 响应数据 公司标准格式
     *
     * @var bool
     */
    protected $contentFormatStd = false;

    /**
     * 返回的除了 data 的值
     *
     * @var array
     */
    public $with = [];

    protected $withList = false;

    /**
     * Create a new response instance.
     *
     * @param mixed $content
     * @param int   $status
     * @param array $headers
     *
     * @return void
     */
    public function __construct($content = '', $status = 200, $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->code = $status;
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @return $this
     */
    public function prepare(Request $request)
    {
        if (!$this->transformedContent) {
            $content = $this->content;
            if ($this->shouldBeJson($this->getOriginalContent())) {
                $content = json_decode($this->content, true);
            }

            $jsonDecode['code']    = $this->getFullCode();
            $jsonDecode['message'] = $this->getMessage();

            $meta = [];
            if (
                $this->getOriginalContent() instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection
                && $this->getOriginalContent()->resource instanceof \Illuminate\Pagination\AbstractPaginator
            ) {
                $meta['pagination'] = Arr::only(
                    $content['meta'] ?? [],
                    ['current_page', 'from', 'last_page', 'per_page', 'total']
                );
                $content            = $content['data'] ?? null;
            }

            if (!is_null($this->original)) {
                $jsonDecode['data'] = $content;
            }

            if (!empty($this->with['meta'])) {
                $meta = array_merge($meta, $this->with['meta'] ?? []);
            }

            if (!empty($meta)) {
                $jsonDecode['meta'] = $meta;
            }

            if ($this->contentFormatStd) {
                $jsonDecode = $this->formatContentToStd($jsonDecode);
            }

            if ($this->contentKeysToCamel && $jsonDecode) {
                $jsonDecode = array_keys_to_camel($jsonDecode);
            }

            if (config('app.debug') && $this->exception) {
                $jsonDecode['debug'] = [
                    'message' => $this->exception->getMessage(),
                    'file'    => $this->exception->getFile(),
                    'line'    => $this->exception->getLine(),
                    'trace'   => $this->exception->getTrace(),
                ];
            }

            $this->content = json_encode($jsonDecode);

            $this->headers->remove('Content-Type');
            $this->headers->set('Content-Type', 'application/json');

            $this->transformedContent = true;
        }

        parent::prepare($request);

        return $this;
    }

    /**
     * 按标准格式 响应
     *
     * @return $this
     */
    public function std()
    {
        $this->enableContentKeysToCamel();
        $this->contentFormatStd = true;

        return $this;
    }

    public function withList()
    {
        $this->withList = true;

        return $this;
    }

    /**
     * @param array $content
     *
     * @return array
     */
    protected function formatContentToStd(array $content): array
    {
        $results = [
            'code' => $content['code'],
            'message' => $content['message'],
        ];

        if (isset($content['meta']['pagination'])) {
            $results['data']['page'] = $content['meta']['pagination'];
            unset($content['meta']['pagination']);
        }

        if (empty($content['meta'])) {
            unset($content['meta']);
        }

        if (isset($content['meta'])) {
            $results['data']['meta'] = $content['meta'];
        }

        if ($this->withList) {
            $results['data']['list'] = $content['data'] ?? [];
        } else {
            $results['data'] = $content['data'] ?? null;
        }

        return $results;
    }


    /**
     * Morph the given content into JSON.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        } elseif ($content instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection
            && $content->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            return $content->response()->getContent();
        }

        return json_encode($content);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        // Attempt to set the content string, if we encounter an unexpected value
        // then we most likely have an object that cannot be type cast. In that
        // case we'll simply leave the content as null and set the original
        // content value and continue.
        try {
            return parent::setContent($content);
        } catch (UnexpectedValueException $exception) {
            $this->original = $content;

            return $this;
        }
    }

    /**
     * Add a meta key and value pair.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function addMeta($key, $value = null)
    {
        $with = $this->with;
        $meta = $with['meta'] ?? [];

        if (is_string($key)) {
            $key = [$key => $value];
        }

        $meta         = array_merge($meta, Arr::wrap($key));
        $with['meta'] = $meta;

        $this->with = $with;

        return $this;
    }

    /**
     * Add a meta key and value pair.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function meta($key, $value = null)
    {
        return $this->addMeta($key, $value);
    }

    /**
     * Set the meta data for the response.
     *
     * @param array $meta
     *
     * @return \Zfwy\Api\Http\Response
     */
    public function setMeta(array $meta)
    {
        $this->with = ['meta' => $meta];

        return $this;
    }

    /**
     * Get the meta data for the response.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->with['meta'] ?? [];
    }

    /**
     * Sets the response status code.
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @final
     */
    public function setStatusCode(int $code, $text = null): object
    {
        return $this->setCode($code);
    }

    /**
     * 设置 code
     *
     * @param int $code
     *
     * @return $this
     */
    public function setCode(int $code = 200): object
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return int
     */
    public function getCode()
    {
        if ($this->code < 0) {
            $this->code = 500;
        } elseif (!$this->code) {
            $this->code = 200;
        }

        return $this->code;
    }

    /**
     * Get full code
     *
     * @return int
     */
    public function getFullCode()
    {
        return (int) (config('app.service_code') . $this->getCode());
    }

    /**
     * 设置 message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message = 'ok')
    {
        $this->message = $message;

        return $this;
    }

    /**
     * 获取 Message
     *
     * @return string
     */
    public function getMessage()
    {
        if (!$this->message) {
            $this->message = trans('exception.' . $this->getCode());
        }

        return $this->message;
    }

    /**
     * 启用 响应内容 key to camel
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableContentKeysToCamel(bool $enable = true)
    {
        $this->contentKeysToCamel = $enable;

        return $this;
    }
}
