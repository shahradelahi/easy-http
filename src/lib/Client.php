<?php

namespace EasyHttp;

use CurlHandle;
use EasyHttp\Model\DownloadResult;
use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;
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
     * Set has self-signed certificate
     *
     * This is used to set the curl option CURLOPT_SSL_VERIFYPEER
     * and CURLOPT_SSL_VERIFYHOST to false. This is useful when you are
     * in local environment, or you have self-signed certificate.
     *
     * @param bool $has
     * @return void
     */
    public static function setHasSelfSignedCertificate(bool $has): void
    {
        define('EZ_CURL_SSL_SELF_SIGNED', $has);
    }

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

            $CurlHandle = $this->createCurlHandler(
                $request['method'],
                $request['uri'],
                $request['options'] ?? []
            );
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
     * @param ?string $method
     * @param string $uri
     * @param array|HttpOptions $options
     * @return ?CurlHandle
     */
    private function createCurlHandler(?string $method, string $uri, array|HttpOptions $options = []): ?CurlHandle
    {
        $cHandler = curl_init();

        if (gettype($options) === 'array') {
            $options = new HttpOptions(
                $this->getOptions($options)
            );
        }

        if (count($options->queries) > 0) {
            if (!str_contains($uri, '?')) $uri .= '?';
            $uri .= $options->getQueryString();
        }

        curl_setopt($cHandler, CURLOPT_URL, $uri);

        $this->setCurlOpts($cHandler, $method, $options);

        return $cHandler;
    }

    /**
     * Setup curl options based on the given method and our options.
     *
     * @param CurlHandle $cHandler
     * @param ?string $method
     * @param HttpOptions $options
     * @return void
     */
    private function setCurlOpts(CurlHandle $cHandler, ?string $method, HttpOptions $options): void
    {
        curl_setopt($cHandler, CURLOPT_HEADER, true);
        curl_setopt($cHandler, CURLOPT_CUSTOMREQUEST, $method ?? 'GET');

        # Fetch the header
        $fetchedHeaders = [];
        foreach ($options->headers as $header => $value) {
            $fetchedHeaders[] = $header . ': ' . $value;
        }

        # Set headers
        if ($fetchedHeaders != []) {
            curl_setopt($cHandler, CURLOPT_HTTPHEADER, $fetchedHeaders);
        }

        # Add body if we have one.
        if ($options->body) {
            curl_setopt($cHandler, CURLOPT_CUSTOMREQUEST, $method ?? 'POST');
            curl_setopt($cHandler, CURLOPT_POSTFIELDS, $options->body);
            curl_setopt($cHandler, CURLOPT_POST, true);
        }

        # Check for a proxy
        if ($options->proxy != null) {
            curl_setopt($cHandler, CURLOPT_PROXY, $options->proxy->getProxy());
            curl_setopt($cHandler, CURLOPT_PROXYUSERPWD, $options->proxy->getAuth());
            if ($options->proxy->type !== null) {
                curl_setopt($cHandler, CURLOPT_PROXYTYPE, $options->proxy->type);
                curl_setopt($cHandler, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
        }

        curl_setopt($cHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cHandler, CURLOPT_FOLLOWLOCATION, true);

        # Add and override the custom curl options.
        if (count($options->curlOptions) > 0) {
            foreach ($options->curlOptions as $option => $value) {
                curl_setopt($cHandler, $option, $value);
            }
        }

        # if we have a timeout, set it.
        if ($options->timeout != null) {
            curl_setopt($cHandler, CURLOPT_TIMEOUT, $options->timeout);
        }

        # If self-signed certs are allowed, set it.
        if (EZ_CURL_SSL_SELF_SIGNED === true) {
            curl_setopt($cHandler, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cHandler, CURLOPT_SSL_VERIFYHOST, false);
        }
    }

    /**
     * Initialize options from array.
     *
     * @param array $options
     * @return array
     */
    private function getOptions(array $options): array
    {
        $defaults = [
            'headers' => [],
            'body' => null,
            'timeout' => null,
            'proxy' => null,
            'curlOptions' => [],
            'queries' => []
        ];

        return array_merge($defaults, $options);
    }

    /**
     * Download large files.
     *
     * This method is used to download large files with
     * creating multiple requests.
     *
     * @param string $url The direct url to the file.
     * @param string $path The path to save the file.
     * @param array|HttpOptions $options The options to use.
     *
     * @return DownloadResult
     */
    public function download(string $url, string $path, array|HttpOptions $options = []): DownloadResult
    {
        return new DownloadResult(); // TODO: Implement download() method.
    }

}