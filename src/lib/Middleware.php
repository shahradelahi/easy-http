<?php

namespace EasyHttp;

use EasyHttp\Model\HttpOptions;
use EasyHttp\Util\Utils;

/**
 * Class Middleware
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Middleware
{

    /**
     * Create curl handler.
     *
     * @param ?string $method
     * @param string $uri
     * @param array|HttpOptions $options
     *
     * @return false|\CurlHandle
     */
    public static function create_curl_handler(?string $method, string $uri, array|HttpOptions $options = []): false|\CurlHandle
    {
        $handler = curl_init();
        if (is_resource($handler) || !$handler) {
            return false;
        }

        if (gettype($options) === 'array') {
            $options = new HttpOptions($options);
        }

        if (count($options->getQuery()) > 0) {
            if (!str_contains($uri, '?')) {
                $uri .= '?';
            }
            $uri .= $options->getQueryString();
        }

        curl_setopt($handler, CURLOPT_URL, $uri);

        self::set_curl_options($handler, $method, $options);

        return $handler;
    }

    /**
     * Setup curl options based on the given method and our options.
     *
     * @param \CurlHandle $cHandler
     * @param ?string $method
     * @param HttpOptions $options
     *
     * @return void
     */
    public static function set_curl_options(\CurlHandle $cHandler, ?string $method, HttpOptions $options): void
    {
        curl_setopt($cHandler, CURLOPT_HEADER, true);
        curl_setopt($cHandler, CURLOPT_CUSTOMREQUEST, $method ?? 'GET');

        # Fetch the header
        $fetchedHeaders = [];
        foreach ($options->getHeader() as $header => $value) {
            $fetchedHeaders[] = $header . ': ' . $value;
        }

        # Set headers
        curl_setopt($cHandler, CURLOPT_HTTPHEADER, $fetchedHeaders ?? []);


        # Add body if we have one.
        if ($options->getBody()) {
            curl_setopt($cHandler, CURLOPT_CUSTOMREQUEST, $method ?? 'POST');
            curl_setopt($cHandler, CURLOPT_POSTFIELDS, $options->getBody());
            curl_setopt($cHandler, CURLOPT_POST, true);
        }

        # Check for a proxy
        if ($options->getProxy() != null) {
            curl_setopt($cHandler, CURLOPT_PROXY, $options->getProxy()->getHost());
            curl_setopt($cHandler, CURLOPT_PROXYUSERPWD, $options->getProxy()->getAuth());
            if ($options->getProxy()->type !== null) {
                curl_setopt($cHandler, CURLOPT_PROXYTYPE, $options->getProxy()->type);
            }
        }

        curl_setopt($cHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cHandler, CURLOPT_FOLLOWLOCATION, true);

        # Add and override the custom curl options.
        foreach ($options->getCurlOptions() as $option => $value) {
            curl_setopt($cHandler, $option, $value);
        }

        # if we have a timeout, set it.
        curl_setopt($cHandler, CURLOPT_TIMEOUT, $options->getTimeout());

        # If self-signed certs are allowed, set it.
        if ((bool)getenv('HAS_SELF_SIGNED_CERT') === true) {
            curl_setopt($cHandler, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cHandler, CURLOPT_SSL_VERIFYHOST, false);
        }

        (new Middleware())->handle_media($cHandler, $options);
    }

    /**
     * Handle the media
     *
     * @param \CurlHandle $handler
     * @param HttpOptions $options
     * @return void
     */
    private function handle_media(\CurlHandle $handler, HttpOptions $options): void
    {
        if (count($options->getMultipart()) > 0) {
            curl_setopt($handler, CURLOPT_POST, true);
            curl_setopt($handler, CURLOPT_CUSTOMREQUEST, 'POST');

            $form_data = new FormData();
            foreach ($options->getMultipart() as $key => $value) {
                $form_data->addFile($key, $value);
            }

            $headers = [];
            foreach ($options->getHeader() as $header => $value) {
                if (Utils::insensitiveString($header, 'content-type')) continue;
                $headers[] = $header . ': ' . $value;
            }
            $headers[] = 'Content-Type: multipart/form-data';

            curl_setopt($handler, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($handler, CURLOPT_POSTFIELDS, $form_data->getFiles());
        }
    }

}