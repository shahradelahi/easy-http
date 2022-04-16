<?php

namespace EasyHttp;

use EasyHttp\Models\HttpOptions;
use EasyHttp\Models\HttpResponse;
use EasyHttp\Traits\ClientTrait;

/**
 * Client
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Client
{

    use ClientTrait;

    /**
     * This method is used to send a http request to a given url.
     *
     * @param string $method
     * @param string $uri
     * @param array|HttpOptions $options
     * @return HttpResponse
     */
    public function request(string $method, string $uri, array|HttpOptions $options = []): HttpResponse
    {
        if (gettype($options) === 'array') {
            $options = new HttpOptions($options);
        }

        $cHandler = curl_init();

        curl_setopt($cHandler, CURLOPT_URL, $uri);
        curl_setopt($cHandler, CURLOPT_HEADER, true);

        $fetchedHeaders = [];
        foreach ($options->headers as $header => $value) {
            $fetchedHeaders[] = $header . ': ' . $value;
        }

        if ($fetchedHeaders != []) {
            curl_setopt($cHandler, CURLOPT_HTTPHEADER, $fetchedHeaders);
        }

        if ($options->body) {
            curl_setopt($cHandler, CURLOPT_POSTFIELDS, $options->body);
            curl_setopt($cHandler, CURLOPT_POST, true);
        }

        curl_setopt($cHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($cHandler);
        curl_close($cHandler);

        return (new HttpResponse())->setResponse([
            'status' => curl_getinfo($cHandler, CURLINFO_HTTP_CODE),
            'body' => curl_exec($cHandler),
            'info' => curl_getinfo($cHandler),
            'error' => curl_error($cHandler),
        ]);
    }


}