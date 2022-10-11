<?php declare(strict_types=1);

namespace EasyHttp;

use Utilities\Common\Traits\hasAssocStorage;

/**
 * Chunk class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 * @method string getIdentifier() Get the identifier
 * @method int getStartByte() Starting byte of the chunk
 * @method int getEndByte() Ending byte of the chunk
 * @method string getLength() Length of the chunk
 * @method string|null getBody() Get the body of the chunk
 * @method int getElapsedTime() The total time in seconds for the chunk to download
 * @method string getLocalPath() The local path of the chunk
 * @method string getStatus() The status of the chunk. returns: success, failed, pending, init
 * @method string|null getErrorCode() The error code of the chunk
 * @method string|null getErrorMessage() The error message if the chunk failed
 *
 * @method Chunk setIdentifier(string $identifier) Set the identifier
 * @method Chunk setStartByte(int $startByte) Set the starting byte of the chunk
 * @method Chunk setEndByte(int $endByte) Set the ending byte of the chunk
 * @method Chunk setLength(int $length) Set the length of the chunk
 * @method Chunk setBody(string|null $body) Set the body of the chunk
 * @method Chunk setElapsedTime(int $elapsedTime) Set the total time in seconds for the chunk to download
 * @method Chunk setLocalPath(string $localPath) Set the local path of the chunk
 * @method Chunk setStatus(string $status) Set the status of the chunk. returns: success, failed, pending, init
 * @method Chunk setErrorCode(string|null $errorCode) Set the error code of the chunk
 * @method Chunk setErrorMessage(string|null $errorMessage) Set the error message if the chunk failed
 */
class Chunk extends \Utilities\Common\Entity
{

    use hasAssocStorage;

    /**
     * The number of retries
     *
     * @var int
     */
    public int $retries = 3;

    /**
     * Chunk constructor.
     *
     * @param array $data {startByte, endByte, localPath}
     */
    public function __construct(array $data = [])
    {
        $id = uniqid('chunk_');
        $temp_dir = sys_get_temp_dir();
        $local_path = $temp_dir . DIRECTORY_SEPARATOR . $id;

        parent::__construct(array_merge([
            'identifier' => $id,
            'startByte' => -1,
            'endByte' => -1,
            'length' => -1,
            'elapsedTime' => -1,
            'localPath' => $local_path,
            'body' => null,
            'status' => 'init',
            'errorCode' => null,
            'errorMessage' => null,
        ], $data));
    }

    /**
     * Save the chunk to the local path
     *
     * @param string|null $localPath [optional] The local path to save the chunk
     * @return bool
     */
    public function save(string $localPath = null): bool
    {
        $localPath = $localPath ?? $this->getLocalPath();
        $body = $this->getBody();
        if (!$body) {
            return false;
        }
        return file_put_contents($localPath, $body) !== false;
    }

    /**
     * Delete the chunk from the local path
     *
     * @param string|null $localPath [optional] The local path to delete the chunk
     * @return bool
     */
    public function delete(string $localPath = null): bool
    {
        $localPath = $localPath ?? $this->getLocalPath();
        return unlink($localPath);
    }

}