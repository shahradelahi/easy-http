<?php

namespace EasyHttp;

use CurlHandle;
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

        $CurlHandle = $this->createCurlHandler($method, $uri, $options);

        curl_exec($CurlHandle);
        curl_close($CurlHandle);

        return (new HttpResponse())->setResponse([
            'status' => curl_getinfo($CurlHandle, CURLINFO_HTTP_CODE),
            'body' => curl_exec($CurlHandle),
            'info' => curl_getinfo($CurlHandle),
            'error' => curl_error($CurlHandle),
        ]);
    }

    /**
     * Send multiple requests to a given url.
     *
     * @param array $requests [{method, uri, options}, ...]
     * @return array<HttpResponse>
     */
    public function bulk(array $requests): array
    {
        $result = [];
        $handlers = [];
        $multi_handler = curl_multi_init();
        foreach ($requests as $request) {

            [$method, $uri, $options] = $request;

            if (gettype($request['options']) === 'array') {
                $options = new HttpOptions($request['options']);
            }

            $CurlHandle = $this->createCurlHandler($method, $uri, $options);
            $handlers[] = $CurlHandle;
            curl_multi_add_handle($multi_handler, $CurlHandle);

        }

        $active = null;
        do {
            $mrc = curl_multi_exec($multi_handler, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        foreach ($handlers as $handler) {
            curl_multi_remove_handle($multi_handler, $handler);
        }
        curl_multi_close($multi_handler);

        foreach ($handlers as $handler) {
            $result[] = (new HttpResponse())->setResponse([
                'status' => curl_getinfo($handler, CURLINFO_HTTP_CODE),
                'body' => curl_exec($handler),
                'info' => curl_getinfo($handler),
                'error' => curl_error($handler),
            ]);
        }

        return $result;
    }

    /**
     * Create curl handler.
     *
     * @param string $method
     * @param string $uri
     * @param array|HttpOptions $options
     * @return ?CurlHandle
     */
    private function createCurlHandler(string $method, string $uri, array|HttpOptions $options = []): ?CurlHandle
    {
        $cHandler = curl_init();

        if (count($options->queries) > 0) {
            if (!str_contains($uri, '?')) $uri .= '?';
            $uri .= $options->getQueryString();
        }

        curl_setopt($cHandler, CURLOPT_URL, $uri);
        curl_setopt($cHandler, CURLOPT_HEADER, true);
        curl_setopt($cHandler, CURLOPT_CUSTOMREQUEST, $method ?? 'GET');

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

        return $cHandler;
    }


}