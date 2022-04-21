<?php

namespace EasyHttp\Model;

/**
 * Http Options
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class HttpOptions
{

    /**
     * @var ?array
     */
    public ?array $headers = [];

    /**
     * @var ?array
     */
    public ?array $queries = [];

    /**
     * @var ?string
     */
    public ?string $body = null;

    /**
     * The proxy server to use
     *
     * @var ?ProxyServer
     */
    public ?ProxyServer $proxy = null;

    /**
     * Add specific opt to curl
     *
     * @var ?array
     */
    public ?array $curlOptions = [];

    /**
     * The timeout of the request
     *
     * @var ?int
     */
    public ?int $timeout = null;

    /**
     * Http Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Returns the class as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'queries' => $this->queries,
            'body' => $this->body,
            'proxy' => $this->proxy,
            'curlOptions' => $this->curlOptions,
            'timeout' => $this->timeout
        ];
    }

    /**
     * Set Options
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            if (method_exists($this, 'set' . ucfirst($key))) {
                if ($value !== null) {
                    $this->{'set' . ucfirst($key)}($value);
                }
            } else {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                } else {
                    throw new \InvalidArgumentException("Invalid option: $key");
                }
            }
        }
    }

    /**
     * Set Body of Http request
     *
     * @param ?string|array $body The body of the request - On array it will be converted to json
     * @return void
     */
    public function setBody(string|array|null $body): void
    {
        if (is_array($body)) {
            $this->body = json_encode($body);
            $this->headers['Content-Type'] = 'application/json';
        } else {
            $this->body = $body;
        }
    }

    /**
     * Set proxy server
     *
     * @param array $proxy ["host", "port", "user", "pass"]
     * @return void
     */
    public function setProxy(array $proxy): void
    {
        $this->proxy = (new ProxyServer())->setProxy($proxy);
    }

    /**
     * Get Query String
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return http_build_query($this->queries);
    }

    /**
     * Set Curl Options
     *
     * @param array $options [{"CURLOPT_*": "value"}, ...]
     * @return void
     */
    public function setCurlOptions(array $options): void
    {
        if (count($options) > 0) {
            foreach ($options as $option => $value) {
                $this->curlOptions[$option] = $value;
            }
        }
    }

}