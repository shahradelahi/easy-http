<?php declare(strict_types=1);

namespace EasyHttp\Model;

use EasyHttp\Chunk;

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
    public int $numberOfChunks;

    /**
     * Start time of the download in microsecond.
     *
     * @var float
     */
    public float $startTime;

    /**
     * End time of the download in microsecond.
     *
     * @var float
     */
    public float $endTime;

    /**
     * The downloaded chunks
     *
     * @var array<\EasyHttp\Chunk>
     */
    public array $chunks;

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
        $chunk = new Chunk([
            'identifier' => $id,
            'body' => $body,
            'elapsedTime' => $elapsedTime,
            'length' => 0,
        ]);
        $save = $this->saveChunk($id, $body);

        $chunk->setStatus($save ? 'downloaded' : 'failed');
        $chunk->setLength(mb_strlen($body));

        $this->chunks[] = $chunk;
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
     * Merge the chunks into a single string
     *
     * @return string
     */
    public function mergeChunks(): string
    {
        $result = '';
        foreach ($this->chunks as $chunk) {
            $result .= file_get_contents($chunk->getLocalPath());
        }
        return $result;
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