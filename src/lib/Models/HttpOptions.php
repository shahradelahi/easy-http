<?php

namespace EasyHttp\Models;

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
     * Http Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
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
            if ($key == 'body') {
                $this->setBody($value);
            } else {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Set Body of Http request
     *
     * @param string|array $body The body of the request - On array it will be converted to json
     * @return void
     */
    public function setBody(string|array $body): void
    {
        if (is_array($body)) {
            $this->body = json_encode($body);
            $this->headers['Content-Type'] = 'application/json';
        } else {
            $this->body = $body;
        }
    }

    /**
     * @var array|null
     */
    public ?array $headers;

    /**
     * @var array|null
     */
    public ?array $queries;

    /**
     * @var string|null
     */
    public ?string $body = null;

}