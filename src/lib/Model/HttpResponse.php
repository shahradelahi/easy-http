<?php

namespace EasyHttp\Model;

use EasyHttp\Enums\CurlInfo;

/**
 * Class HttpResponse
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 *
 * @method getBody()                                    This method returns the body of the response.
 * @method setBody($body)                               This method sets the body of the response.
 * @method setHeaderSize($size)                         This method sets the header size of the response.
 * @method getHeaderSize()                              This method returns the header size of the response.
 * @method getHeaders()                                 This method returns the headers of the response.
 * @method getStatusCode()                              This method returns the status code of the response.
 * @method setStatusCode($code)                         This method sets the status code of the response.
 * @method getError()                                   This method returns the error of the response.
 * @method setError($error)                             This method sets the error of the response.
 * @method getErrorCode()                               This method returns the error code of the response.
 * @method setErrorCode($code)                          This method sets the error code of the response.
 */
class HttpResponse
{

    /**
     * The Handler
     *
     * @var \CurlHandle
     */
    private \CurlHandle $curlHandle;

    /**
     * @var int
     */
    private int $status;

    /**
     * @var int
     */
    private int $headerSize;

    /**
     * @var array
     */
    private array $headers;

    /**
     * @var ?string
     */
    private ?string $body;

    /**
     * @var mixed
     */
    private mixed $info;

    /**
     * @var string
     */
    private string $error;

    /**
     * The Error Code
     *
     * @var int
     */
    private int $errorCode;

    /**
     * Set the curl handle
     *
     * @param \CurlHandle $curlHandle
     * @return HttpResponse
     */
    public function setCurlHandle(\CurlHandle $curlHandle): HttpResponse
    {
        $this->curlHandle = $curlHandle;
        return $this;
    }

    /**
     * Get info from the curl handle
     *
     * @return mixed
     */
    public function getCurlInfo(): CurlInfo
    {
        if (empty($this->curlHandle)) return false;
        return new CurlInfo(curl_getinfo($this->curlHandle));
    }

    /**
     * Set the headers of the response
     *
     * @param string $headers
     * @return HttpResponse
     */
    public function setHeaders(string $headers): HttpResponse
    {
        $result = [];
        $lines = explode("\r\n", $headers);
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                $parts = explode(':', $line);
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }
        $this->headers = $result;
        return $this;
    }

    /**
     * Get a key from the response headers
     *
     * @param string $key
     * @return mixed
     */
    public function getHeaderLine(string $key): mixed
    {
        return array_change_key_case($this->headers, CASE_LOWER)[strtolower($key)] ?? null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }

        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, 3));
            return $this->{$property} ?? null;
        }

        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, 3));
            $this->{$property} = $arguments[0] ?? null;
            return $this;
        }

        throw new \BadMethodCallException("Method $name does not exist");
    }

}