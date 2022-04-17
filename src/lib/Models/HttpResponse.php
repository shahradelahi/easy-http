<?php

namespace EasyHttp\Models;

/**
 * Http Response
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class HttpResponse
{

    /**
     * Set response
     *
     * @param array $input ["status", "headers", "body", "info", "error"]
     * @return HttpResponse
     */
    public function setResponse(array $input): self
    {
        foreach ($input as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }

    /**
     * @var int
     */
    public int $status;

    /**
     * @var array
     */
    public array $headers;

    /**
     * @var bool|string
     */
    public bool|string $body;

    /**
     * @var mixed
     */
    public mixed $info;

    /**
     * @var string
     */
    public string $error;

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Get body
     *
     * @return ?string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Get a key from the response headers
     *
     * @param string $key
     * @return mixed
     */
    public function getHeaderLine(string $key): mixed
    {
        return $this->headers[$key] ?? null;
    }

}