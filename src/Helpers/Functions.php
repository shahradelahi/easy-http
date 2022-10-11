<?php declare(strict_types=1);

namespace EasyHttp;

use EasyHttp\Model\DownloadResult;
use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;
use Symfony\Component\Mime\MimeTypes;

if (!function_exists('randomAgent')) {
    /**
     * Get a random user agent
     *
     * @param string|null $type {chrome, firefox, explorer, safari, opera, android, iphone, ipad, ipod} default: random
     * @return string
     */
    function randomAgent(string $type = null): string
    {
        return (new AgentGenerator())->generate($type);
    }
}

if (!function_exists('getFileExtension')) {
    /**
     * Get file extension.
     *
     * @param string $filename The absolute path to the file.
     * @return string eg. jpg
     */
    function getFileExtension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
}

if (!function_exists('getFileMime')) {
    /**
     * Get filetype with the extension.
     *
     * @param string $filename The absolute path to the file.
     * @return string eg. image/jpeg
     */
    function getFileMime(string $filename): string
    {
        $extension = getFileExtension($filename);
        return (new MimeTypes())->getMimeTypes($extension)[0] ?? 'application/octet-stream';
    }
}

if (!function_exists('getFileSize')) {
    /**
     * Get file size.
     *
     * @param string $url The direct url to the file.
     * @return int
     */
    function getFileSize(string $url): int
    {
        if (file_exists($url)) {
            return filesize($url);
        }

        $response = (new HttpClient())->get($url, [
            'headers' => [
                'User-Agent' => randomAgent(),
                'Range' => 'bytes=0-1'
            ],
            'CurlOptions' => [
                CURLOPT_NOBODY => true,
            ]
        ]);

        return (int)$response->getHeaderLine('Content-Length') ?? 0;
    }
}

if (!function_exists('getChunkSize')) {
    /**
     * Get the size of each chunk.
     *
     * For default, we're dividing filesize to 10 as max size of each chunk.
     * If the file size was smaller than 2MB, we'll use the filesize as single chunk.
     *
     * @param int $fileSize The file size.
     * @param int $maxChunks The max number of chunks. (default: 10)
     * @return int
     */
    function getChunkSize(int $fileSize, int $maxChunks = 10): int
    {
        $maxChunkSize = $fileSize / $maxChunks;

        if ($fileSize <= 2 * 1024 * 1024) {
            return $fileSize;
        }

        return min($maxChunkSize, $fileSize);
    }
}

if (!function_exists('downloadChunk')) {
    /**
     * Download a chunk of the file.
     *
     * @param string $url The direct url to the file.
     * @param int $start The start of the chunk.
     * @param int $end The end of the chunk.
     * @param array|HttpOptions $options The options to use.
     *
     * @return Chunk
     */
    function downloadChunk(string $url, int $start, int $end, array|HttpOptions $options = []): Chunk
    {
        if (gettype($options) === 'array') {
            $options = new HttpOptions($options);
        }

        $chunk = (new Chunk())->setStartByte($start)->setEndByte($end);

        $response = (new HttpClient())->get($url, array_merge($options->toArray(), [
            'CurlOptions' => [
                CURLOPT_RANGE => $start . '-' . $end
            ],
        ]));

        $chunk->setBody($response->getBody());
        $micro = $response->getInfo()->getTotalTime();
        $chunk->setElapsedTime($micro / 1000);

        return $chunk;
    }
}

if (!function_exists('download')) {
    /**
     * Download large files.
     *
     * This method is used to download large files with creating multiple requests.
     *
     * Change `max_chunk_count` variable to change the number of chunks. (default: 10)
     *
     * @param string $url The direct url to the file.
     * @param array|HttpOptions $options The options to use.
     *
     * @return DownloadResult
     */
    function download(string $url, array|HttpOptions $options = []): DownloadResult
    {
        $tempDir = $options['temp_dir'] ?? $_SERVER['TEMP'] ?? null;
        $maxChunkCount = $options['max_chunk_count'] ?? 10;

        if (!$tempDir) {
            throw new \RuntimeException("The temp directory is not defined.");
        }

        if (gettype($options) === 'array') {
            $options = new HttpOptions($options);
        }

        $fileSize = getFileSize($url);
        $chunkSize = getChunkSize($fileSize);

        $result = new DownloadResult();

        $result->id = uniqid();
        $result->chunksPath = $tempDir . '/' . $result->id . '/';
        mkdir($result->chunksPath, 0777, true);

        $result->fileSize = $fileSize;
        $result->chunkSize = $chunkSize;
        $result->numberOfChunks = (int)ceil($fileSize / $chunkSize);

        $result->startTime = microtime(true);

        $requests = [];
        for ($i = 0; $i < $result->numberOfChunks; $i++) {
            $range = $i * $chunkSize . '-' . ($i + 1) * $chunkSize;
            if ($i + 1 === $result->numberOfChunks) {
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

        $client = new HttpClient();

        foreach ($client->bulk($requests) as $response) {
            $chunk = new Chunk();
            $chunk->setBody($response->getBody());
            $micro = $response->getInfo()->getTotalTime();
            $chunk->setElapsedTime($micro / 1000);

            $result->chunks[] = $chunk;
        }

        $result->endTime = microtime(true);

        return $result;
    }
}

if (!function_exists('upload')) {
    /**
     * Upload single or multiple files
     *
     * This method is sending file with request method of POST and
     * Content-Type of multipart/form-data.
     *
     * @param string $url The direct url to the file.
     * @param array $filePath The path to the file.
     * @param array|HttpOptions $options The options to use.
     *
     * @return HttpResponse
     */
    function upload(string $url, array $filePath, array|HttpOptions $options = []): HttpResponse
    {
        if (gettype($options) === 'array') {
            $options = new HttpOptions($options);
        }

        $multipart = [];

        foreach ($filePath as $key => $file) {
            $multipart[$key] = new \CURLFile(realpath($file), getFileMime($file));
        }

        $options->setMultipart($multipart);

        return (new HttpClient())->post($url, $options);
    }
}