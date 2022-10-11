<?php declare(strict_types=1);

namespace EasyHttp;

use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;
use EasyHttp\Traits\HttpClientFace;
use RuntimeException;

/**
 * Client class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class HttpClient
{

    use HttpClientFace;

    /**
     * The constructor of the client
     */
    public function __construct()
    {
        $this->tempDir = $_SERVER['TEMP'] ?? null;
        $this->setHasSelfSignedCertificate(true);
    }

    /**
     * Set has self-signed certificate
     *
     * This is used to set the curl option CURLOPT_SSL_VERIFYPEER
     * and CURLOPT_SSL_VERIFYHOST to false. This is useful when you are
     * in local environment, or you have self-signed certificate.
     *
     * @param bool $has
     *
     * @return void
     */
    public function setHasSelfSignedCertificate(bool $has): void
    {
        putenv('HAS_SELF_SIGNED_CERT=' . ($has ? 'true' : 'false'));
    }

    /**
     * This method is used to send a http request to a given url.
     *
     * @param string $method
     * @param string $uri
     * @param array|HttpOptions $options
     *
     * @return HttpResponse
     */
    public function request(string $method, string $uri, array|HttpOptions $options = []): HttpResponse
    {
        $CurlHandle = Middleware::create_curl_handler($method, $uri, $options);
        if (!$CurlHandle) {
            throw new RuntimeException('An error occurred while creating the curl handler');
        }

        $result = new HttpResponse();
        $result->setCurlHandle($CurlHandle);

        $response = curl_exec($CurlHandle);
        if (curl_errno($CurlHandle) || !$response) {
            throw new RuntimeException(
                sprintf('An error occurred while sending the request: %s', curl_error($CurlHandle)),
                curl_errno($CurlHandle)
            );
        }

        $result->setStatusCode(curl_getinfo($CurlHandle, CURLINFO_HTTP_CODE));
        $result->setHeaderSize(curl_getinfo($CurlHandle, CURLINFO_HEADER_SIZE));
        $result->setHeaders(substr((string)$response, 0, $result->getHeaderSize()));
        $result->setBody(substr((string)$response, $result->getHeaderSize()));

        curl_close($CurlHandle);

        return $result;
    }

    /**
     * Send multiple requests to a given url.
     *
     * @param array $requests [{method, uri, options}, ...]
     *
     * @return array<HttpResponse>
     */
    public function bulk(array $requests): array
    {
        $result = [];
        $handlers = [];
        $multi_handler = curl_multi_init();
        foreach ($requests as $request) {

            $CurlHandle = Middleware::create_curl_handler(
                $request['method'] ?? null,
                $request['uri'],
                $request['options'] ?? []
            );
            if (!$CurlHandle) {
                throw new RuntimeException(
                    'An error occurred while creating the curl handler'
                );
            }
            $handlers[] = $CurlHandle;
            curl_multi_add_handle($multi_handler, $CurlHandle);

        }

        $active = -1;
        do {
            $mrc = curl_multi_exec($multi_handler, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi_handler) != -1) {
                do {
                    $mrc = curl_multi_exec($multi_handler, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($handlers as $handler) {
            curl_multi_remove_handle($multi_handler, $handler);
        }
        curl_multi_close($multi_handler);

        foreach ($handlers as $handler) {
            $content = curl_multi_getcontent($handler);
            $response = new HttpResponse();

            if (curl_errno($handler)) {
                throw new RuntimeException(
                    sprintf('An error occurred while sending the request: %s', curl_error($handler)),
                    curl_errno($handler)
                );
            }

            $response->setCurlHandle($handler);
            $response->setStatusCode(curl_getinfo($handler, CURLINFO_HTTP_CODE));
            $response->setHeaderSize(curl_getinfo($handler, CURLINFO_HEADER_SIZE));
            $response->setHeaders(substr($content, 0, $response->getHeaderSize()));
            $response->setBody(substr($content, $response->getHeaderSize()));

            $result[] = $response;
        }

        return $result;
    }

}