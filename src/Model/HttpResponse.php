<?php

namespace EasyHttp\Model;

use CurlHandle;
use EasyHttp\Enums\CurlInfo;

/**
 * HttpResponse class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 * @property int $status                The response code
 * @property string $body               The body of the response
 * @property array $headers             The headers of the response
 * @property int $headerSize            The size of the headers
 * @property CurlHandle $curlHandle     The curl handle
 *
 * @method int        getStatusCode()       This method returns the status code of the response.
 * @method string     getBody()             This method returns the body of the response.
 * @method int        getHeaderSize()       This method returns the header size of the response.
 * @method array      getHeaders()          This method returns the headers of the response.
 * @method string     getErrorMessage()     This method returns the error message of the response.
 * @method int        getErrorCode()        This method returns the error code of the response.
 * @method CurlHandle getCurlHandle()       This method returns the curl handle of the response.
 *
 * @method $this setBody($body)             This method sets the body of the response.
 * @method $this setHeaderSize($size)       This method sets the header size of the response.
 * @method $this setStatusCode($code)       This method sets the status code of the response.
 * @method $this setErrorMessage($error)    This method sets the error of the response.
 * @method $this setErrorCode($code)        This method sets the error code of the response.
 */
class HttpResponse
{

	/**
	 * Set the curl handle
	 *
	 * @param \CurlHandle $curlHandle
	 * @return HttpResponse
	 */
	public function setCurlHandle(\CurlHandle $curlHandle): HttpResponse
	{
		$this->curlHandle = $curlHandle;
		return $this;
	}

	/**
	 * Get info from the curl handle
	 *
	 * @return CurlInfo|false
	 */
	public function getInfoFromCurl(): CurlInfo|false
	{
		if (empty($this->getCurlHandle())) {
			return false;
		}

		return new CurlInfo(curl_getinfo($this->curlHandle));
	}

	/**
	 * Set the headers of the response
	 *
	 * @param string $headers
	 * @return HttpResponse
	 */
	public function setHeaders(string $headers): HttpResponse
	{
		$result = [];
		$lines = explode("\r\n", $headers);
		foreach ($lines as $line) {
			if (str_contains($line, ':')) {
				$parts = explode(':', $line);
				$result[trim($parts[0])] = trim($parts[1]);
			}
		}
		$this->headers = $result;
		return $this;
	}

	/**
	 * Get a key from the response headers
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getHeaderLine(string $key): mixed
	{
		return array_change_key_case($this->headers, CASE_LOWER)[strtolower($key)] ?? null;
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments): mixed
	{
		if (property_exists($this, $name)) {
			return $this->{$name};
		}

		if (method_exists($this, $name)) {
			return $this->{$name}();
		}

		if (str_starts_with($name, 'get')) {
			$property = lcfirst(substr($name, 3));
			return $this->{$property} ?? null;
		}

		if (str_starts_with($name, 'set')) {
			$property = lcfirst(substr($name, 3));
			$this->{$property} = $arguments[0] ?? null;
			return $this;
		}

		throw new \BadMethodCallException("Method $name does not exist");
	}

}