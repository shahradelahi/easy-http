<?php

namespace EasyHttp\Model;

/**
 * DownloadResult class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class DownloadResult
{

	/**
	 * The unique identifier of the download
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * The final size of the downloaded file
	 *
	 * @var int
	 */
	public int $fileSize;

	/**
	 * The path of the downloaded chunks
	 *
	 * @var string
	 */
	public string $chunksPath;

	/**
	 * The size of each chunk
	 *
	 * @var int
	 */
	public int $chunkSize;

	/**
	 * The count of chunks
	 *
	 * @var int
	 */
	public int $chunks;

	/**
	 * Start time of the download in timestamp.
	 *
	 * @var int
	 */
	public int $startTime;

	/**
	 * End time of the download in timestamp.
	 *
	 * @var int
	 */
	public int $endTime;

	/**
	 * The downloaded chunks
	 *
	 * @var array [{id, size, location, elapsedTime}, ...]
	 */
	public array $downloads;

	/**
	 * Add a chunk to the download result
	 *
	 * @param string $id The unique identifier of the chunk
	 * @param ?string $body The chunk body
	 * @param float $elapsedTime in microseconds
	 * @return void
	 */
	public function addChunk(string $id, ?string $body, float $elapsedTime): void
	{
		$data = [
			'id' => $id,
			'location' => null,
			'size' => 0,
			'elapsed_time' => $elapsedTime,
			'status' => 'failed',
		];
		if ($body !== null) {
			$save = $this->saveChunk($id, $body);
			$data = array_merge($data, [
				'location' => $save ?? null,
				'size' => strlen($body),
				'status' => $save ? 'saved' : 'failed',
			]);
		}
		$this->downloads[] = $data;
	}

	/**
	 * Save the chunks to the temp directory
	 *
	 * @param string $id The unique identifier of the chunk
	 * @param string $body The body of the chunk
	 * @return string|bool
	 */
	private function saveChunk(string $id, string $body): string|bool
	{
		$path = $this->chunksPath . DIRECTORY_SEPARATOR . $id;
		return file_put_contents($path, $body) ? $path : false;
	}

	/**
	 * Merge the chunks into a single string
	 *
	 * @return string
	 */
	public function mergeChunks(): string
	{
		$result = '';
		foreach ($this->downloads as $chunk) {
			$result .= file_get_contents($chunk['location']);
		}
		return $result;
	}

	/**
	 * Save the merged chunks to a file
	 *
	 * @param string $filePath The path/to/file.ext
	 * @return bool
	 */
	public function save(string $filePath): bool
	{
		$pathInfo = pathinfo($filePath, PATHINFO_DIRNAME);
		if (gettype($pathInfo) != "string") $pathInfo = $pathInfo['dirname'];
		if (!file_exists($pathInfo)) {
			throw new \InvalidArgumentException('The directory does not exist');
		}
		$result = $this->mergeChunks();
		$this->cleanChunks();
		return file_put_contents($filePath, $result) !== false;
	}

	/**
	 * Clean the directory of the chunks
	 *
	 * @return void
	 */
	public function cleanChunks(): void
	{
		foreach (glob($this->chunksPath . DIRECTORY_SEPARATOR . '*') as $file) {
			unlink($file);
		}
		rmdir($this->chunksPath);
	}

}