<?php

namespace EasyHttp;

/**
 * Class Request
 *
 * This class is used to make HTTP requests interface.
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 * @method static Request request(string $method, string $url, array $options = [])
 * @method static Request get(string $url, array $options = [])
 * @method static Request post(string $url, array $options = [])
 * @method static Request put(string $url, array $options = [])
 * @method static Request patch(string $url, array $options = [])
 * @method static Request delete(string $url, array $options = [])
 */
class Request
{

    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var array
     */
    private array $options;

    /**
     * Get array of properties
     *
     * @return array
     */
    public function getProperties(): array
    {
        return [
            'method' => $this->method,
            'url' => $this->url,
            'options' => $this->options
        ];
    }

    /**
     *
     * @param string $method
     * @param string $url
     * @param array $options
     */
    public function __construct(string $method, string $url, array $options = [])
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * If the method is not defined, it will return the Request class.
     * otherwise it will return the response of the request.
     *
     * @param string $name The method name
     * @param array $arguments The method arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (in_array($name, ['request', 'get', 'post', 'put', 'patch', 'delete'])) {
            return (new Client())->{$name}(...$arguments);
        }
        return $this->{$name}(...$arguments);
    }

}