<?php

namespace EasyHttp\Enums;

/**
 * CurlInfo class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class CurlInfo
{

	/**
	 * @var string
	 */
	public string $URL;

	/**
	 * @var string
	 */
	public string $CONTENT_TYPE;

	/**
	 * @var int
	 */
	public int $HTTP_CODE;

	/**
	 * @var int
	 */
	public int $HEADER_SIZE;

	/**
	 * @var int
	 */
	public int $REQUEST_SIZE;

	/**
	 * @var int
	 */
	public int $FILETIME;

	/**
	 * @var int
	 */
	public int $SSL_VERIFY_RESULT;

	/**
	 * @var int
	 */
	public int $REDIRECT_COUNT;

	/**
	 * @var float
	 */
	public float $TOTAL_TIME;

	/**
	 * @var float
	 */
	public float $NAMELOOKUP_TIME;

	/**
	 * @var float
	 */
	public float $CONNECT_TIME;

	/**
	 * @var float
	 */
	public float $PRETRANSFER_TIME;

	/**
	 * @var int
	 */
	public int $SIZE_UPLOAD;

	/**
	 * @var int
	 */
	public int $SIZE_DOWNLOAD;

	/**
	 * @var int
	 */
	public int $SPEED_DOWNLOAD;

	/**
	 * @var int
	 */
	public int $SPEED_UPLOAD;

	/**
	 * @var int
	 */
	public int $DOWNLOAD_CONTENT_LENGTH;

	/**
	 * @var int
	 */
	public int $UPLOAD_CONTENT_LENGTH;

	/**
	 * @var float
	 */
	public float $STARTTRANSFER_TIME;

	/**
	 * @var int
	 */
	public int $REDIRECT_TIME;

	/**
	 * @var string
	 */
	public string $REDIRECT_URL;

	/**
	 * @var string
	 */
	public string $PRIMARY_IP;

	/**
	 * @var array
	 */
	public array $CERTINFO;

	/**
	 * @var int
	 */
	public int $PRIMARY_PORT;

	/**
	 * @var string
	 */
	public string $LOCAL_IP;

	/**
	 * @var int
	 */
	public int $LOCAL_PORT;

	/**
	 * @var int
	 */
	public int $HTTP_VERSION;

	/**
	 * @var int
	 */
	public int $PROTOCOL;

	/**
	 * @var int
	 */
	public int $SSL_VERIFYRESULT;

	/**
	 * @var string
	 */
	public string $SCHEME;

	/**
	 * @var int
	 */
	public int $APPCONNECT_TIME_US;

	/**
	 * @var int
	 */
	public int $CONNECT_TIME_US;

	/**
	 * @var int
	 */
	public int $NAMELOOKUP_TIME_US;

	/**
	 * @var int
	 */
	public int $PRETRANSFER_TIME_US;

	/**
	 * @var int
	 */
	public int $REDIRECT_TIME_US;

	/**
	 * @var int
	 */
	public int $STARTTRANSFER_TIME_US;

	/**
	 * @var int
	 */
	public int $TOTAL_TIME_US;

	/**
	 * The Constructor
	 *
	 * @param array $curlInfo
	 * @return void
	 */
	public function __construct(array $curlInfo = [])
	{
		$this->set($curlInfo);
	}

	/**
	 * Set CurlInfo
	 *
	 * @param array $curlInfo
	 * @return void
	 */
	public function set(array $curlInfo): void
	{
		foreach ($curlInfo as $key => $value) {
			if (property_exists($this, strtoupper($key))) {
				$this->{strtoupper($key)} = $value;
			}
		}
	}

}