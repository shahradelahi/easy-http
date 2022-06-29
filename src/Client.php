<?php

namespace EasyHttp;

use EasyHttp\Enums\ErrorCode;
use EasyHttp\Model\DownloadResult;
use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;
use EasyHttp\Traits\ClientTrait;
use EasyHttp\Utils\Toolkit;
use InvalidArgumentException;
use RuntimeException;

/**
 * Client class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Client
{

	use ClientTrait;

	/**
	 * The temp directory to download files - default is $_SERVER['TEMP']
	 *
	 * @var ?string
	 */
	private ?string $tempDir;

	/**
	 * The Max count of chunk to download file
	 *
	 * @var int
	 */
	public int $maxChunkCount = 10;

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
	 * Set the temporary directory path to save the downloaded files
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function setTempPath(string $path): void
	{
		if (!file_exists($path)) {
			throw new InvalidArgumentException(
				sprintf('The path "%s" does not exist', $path)
			);
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
		$CurlHandle = Middleware::create_curl_handler($method, $uri, $options);
		if (!$CurlHandle) {
			throw new RuntimeException('An error occurred while creating the curl handler');
		}

		$result = new HttpResponse();
		$result->setCurlHandle($CurlHandle);

		$response = curl_exec($CurlHandle);
		if (curl_errno($CurlHandle) || !$response) {
			$result->setErrorCode(curl_errno($CurlHandle));
			$result->setErrorMessage(curl_error($CurlHandle) ?? ErrorCode::getMessage(curl_errno($CurlHandle)));
			return $result;
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
				$response->setErrorCode(curl_errno($handler));
				$response->setErrorMessage(
					curl_error($handler) ??
					ErrorCode::getMessage(curl_errno($handler))
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
	public function download(string $url, array|HttpOptions $options = []): DownloadResult
	{
		if (empty($this->tempDir)) {
			throw new RuntimeException(
				'The temp directory is not set. Please set the temp directory using the `setTempDir` method.'
			);
		}

		if (!file_exists($this->tempDir)) {
			if (mkdir($this->tempDir, 0777, true) === false) {
				throw new RuntimeException(
					'The temp directory is not writable. Please set the temp directory using the `setTempDir` method.'
				);
			}
		}

		if (gettype($options) === 'array') {
			$options = new HttpOptions($options);
		}

		$fileSize = $this->get_file_size($url);
		$chunkSize = $this->get_chunk_size($fileSize);

		$result = new DownloadResult();

		$result->id = uniqid();
		$result->chunksPath = $this->tempDir . '/' . $result->id . '/';
		mkdir($result->chunksPath, 0777, true);

		$result->fileSize = $fileSize;
		$result->chunkSize = $chunkSize;
		$result->chunks = ceil($fileSize / $chunkSize);

		$result->startTime = time();

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
				Toolkit::randomString(16),
				$response->getBody(),
				$response->getInfoFromCurl()->TOTAL_TIME
			);
		}

		$result->endTime = time();

		return $result;
	}

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
	public function upload(string $url, array $filePath, array|HttpOptions $options = []): HttpResponse
	{
		if (gettype($options) === 'array') {
			$options = new HttpOptions($options);
		}

		$multipart = [];

		foreach ($filePath as $key => $file) {
			$multipart[$key] = new \CURLFile(
				realpath($file),
				$this->get_file_type($file)
			);
		}

		$options->setMultipart($multipart);
		return $this->post($url, $options);
	}

	/**
	 * Get filetype with the extension.
	 *
	 * @param string $filename The absolute path to the file.
	 * @return string eg. image/jpeg
	 */
	public static function get_file_type(string $filename): string
	{
		return MimeType::TYPES[pathinfo($filename, PATHINFO_EXTENSION)] ?? 'application/octet-stream';
	}

	/**
	 * Get file size.
	 *
	 * @param string $url The direct url to the file.
	 * @return int
	 */
	public function get_file_size(string $url): int
	{
		if (file_exists($url)) {
			return filesize($url);
		}

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
	 * For default, we're dividing filesize to 10 as max size of each chunk.
	 * If the file size was smaller than 2MB, we'll use the filesize as single chunk.
	 *
	 * @param int $fileSize The file size.
	 * @return int
	 */
	private function get_chunk_size(int $fileSize): int
	{
		$maxChunkSize = $fileSize / $this->maxChunkCount;

		if ($fileSize <= 2 * 1024 * 1024) {
			return $fileSize;
		}

		return min($maxChunkSize, $fileSize);
	}

}