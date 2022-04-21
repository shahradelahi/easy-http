<?php

namespace EasyHttp\Model;

use EasyHttp\Util\Utils;

/**
 * Class UploadResult
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class UploadResult
{

    /**
     * The unique identifier of the uploaded files.
     *
     * @var string
     */
    public string $id;

    /**
     * Start time of the upload in microseconds
     *
     * @var float
     */
    public float $startTime;

    /**
     * End time of the upload in microseconds
     *
     * @var float
     */
    public float $endTime;

    /**
     * The upload status
     *
     * @var bool
     */
    public bool $success = false;

    /**
     * The uploaded files
     *
     * @var array [{id, location, elapsedTime, status}, ...]
     */
    public array $uploads = [];

    /**
     * HttpResponse
     *
     * @var HttpResponse
     */
    public HttpResponse $response;

    /**
     * The Constructor of the UploadResult class
     */
    public function __construct()
    {
        $this->id = Utils::randomString(16);
    }

    /**
     * Add a file to the upload result
     *
     * @param string $location
     * @param float $elapsedTime
     * @param string $status
     *
     * @return void
     */
    public function addFile(string $location, float $elapsedTime, string $status): void
    {
        $this->uploads[] = [
            'id' => Utils::randomString(16),
            'location' => $location,
            'elapsedTime' => $elapsedTime,
            'status' => $status
        ];
    }

}