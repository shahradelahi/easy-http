<?php

namespace EasyHttp;

use CurlHandle;
use EasyHttp\Model\DownloadResult;
use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;
use EasyHttp\Traits\ClientTrait;
use EasyHttp\Util\Utils;

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
     * This variable is used to defied is certificate self-signed or not
     *
     * @var bool
     */
    private bool $isSelfSigned = true;

    /**
     * The temp directory to download files - default is $_SERVER['TEMP']
     *
     * @var ?string
     */
    private ?string $tempDir;

    /**
     * The constructor of the client
     */
    public function __construct()
    {
        $this->tempDir = $_SERVER['TEMP'] ?? null;
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
        $this->isSelfSigned = $has;
    }

    /**
     * Set the temporary directory path to save the downloaded files
     *
     * @param string $path
     *
     * @return void
     */
    public function setTempPath(string $path): void
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('The directory path is not exists');
        }
        $this->tempDir = $path;
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
        $CurlHandle = $this->createCurlHandler($method, $uri, $options);

        $result = new HttpResponse();
        $result->setCurlHandle($CurlHandle);

        $response = curl_exec($CurlHandle);
        if (curl_errno($CurlHandle)) {
            $result->setError(curl_error($CurlHandle));
            $result->setErrorCode(curl_errno($CurlHandle));
            return $result;
        }

        $result->setStatusCode(curl_getinfo($CurlHandle, CURLINFO_HTTP_CODE));
        $result->setHeaderSize(curl_getinfo($CurlHandle, CURLINFO_HEADER_SIZE));
        $result->setHeaders(substr($response, 0, $result->getHeaderSize()));
        $result->setBody(substr($response, $result->getHeaderSize()));

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

            $CurlHandle = $this->createCurlHandler(
                $request['method'] ?? null,
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
                $response->setError(curl_error($handler));
                $response->setErrorCode(curl_errno($handler));
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

    /**
     * Create curl handler.
     *
     * @param ?string $method
     * @param string $uri
     * @param array|HttpOptions $options
     *
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
     *
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
        if ($this->isSelfSigned === true) {
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
     * @param array|HttpOptions $options The options to use.
     *
     * @return DownloadResult
     */
    public function download(string $url, array|HttpOptions $options = []): DownloadResult
    {
        if (empty($this->tempDir)) {
            throw new \RuntimeException('No temp directory set.');
        }

        if (!file_exists($this->tempDir)) {
            if (mkdir($this->tempDir, 0777, true) === false) {
                throw new \RuntimeException('Could not create temp directory.');
            }
        }

        if (gettype($options) === 'array') {
            $options = new HttpOptions(
                $this->getOptions($options)
            );
        }

        $fileSize = $this->getFileSize($url);
        $chunkSize = $this->getChunkSize($fileSize);

        $result = new DownloadResult();

        $result->id = uniqid();
        $result->chunksPath = $this->tempDir . '/' . $result->id . '/';
        mkdir($result->chunksPath, 0777, true);

        $result->fileSize = $fileSize;
        $result->chunkSize = $chunkSize;
        $result->chunks = ceil($fileSize / $chunkSize);

        $result->startTime = microtime(true);

        $requests = [];
        for ($i = 0; $i < $result->chunks; $i++) {
            $range = $i * $chunkSize . '-' . ($i + 1) * $chunkSize;
            if ($i + 1 === $result->chunks) {
                $range = $i * $chunkSize . '-' . $fileSize;
            }
            $requests[] = [
                'method' => 'GET',
                'uri' => $url,
                'options' => array_merge($options->toArray(), [
                    'CurlOptions' => [
                        CURLOPT_RANGE => $range
                    ],
                ])
            ];
        }

        foreach ($this->bulk($requests) as $response) {
            $result->addChunk(
                Utils::randomString(16),
                $response->getBody(),
                $response->getCurlInfo()->TOTAL_TIME
            );
        }

        $result->endTime = microtime(true);

        return $result;
    }

    /**
     * Download a chunk of a file.
     *
     * @param DownloadResult $result The result object.
     * @param HttpOptions $options The options to use.
     * @param array $input ['id', 'url', 'range'=> "start-end"]
     *
     * @return void
     */
    private function downloadChunk(DownloadResult &$result, HttpOptions $options, array $input): void
    {
        $options->setCurlOptions([
            CURLOPT_RANGE => $input['range']
        ]);
        $response = self::get($input['url'], $options);
        $result->addChunk(
            $input['id'],
            $response->getBody(),
            $response->getCurlInfo()->TOTAL_TIME
        );
    }

    /**
     * Get file size.
     *
     * @param string $url The direct url to the file.
     * @return int
     */
    private function getFileSize(string $url): int
    {
        $response = $this->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
            ],
            'CurlOptions' => [
                CURLOPT_NOBODY => true,
            ]
        ]);

        return (int)$response->getHeaderLine('Content-Length') ?? 0;
    }

    /**
     * Get the size of each chunk.
     *
     * For default, we're using (fileSize/5) as max chunk size. If the file size
     * is smaller than 5MB, we'll use the file size as chunk size.
     *
     * @param int $fileSize The file size.
     * @param ?int $maxChunkSize The maximum chunk size. (default: fileSize/5)
     *
     * @return int
     */
    private function getChunkSize(int $fileSize, int $maxChunkSize = null): int
    {
        if ($maxChunkSize === null) {
            $maxChunkSize = $fileSize / 5;
        }

        if ($fileSize <= 5 * 1024 * 1024) {
            return $fileSize;
        }

        return min($maxChunkSize, $fileSize);
    }

}