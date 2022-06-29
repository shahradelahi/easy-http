<?php

namespace EasyHttp\Traits;

use EasyHttp\Model\HttpOptions;
use EasyHttp\Model\HttpResponse;

/**
 * ClientTrait class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
trait ClientTrait
{

	/**
	 * Create and send an HTTP request.
	 *
	 * @param string $method HTTP method.
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	abstract public function request(string $method, string $uri, HttpOptions|array $options = []): HttpResponse;

	/**
	 * Create and send an HTTP GET request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function get(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('GET', $uri, $options);
	}

	/**
	 * Create and send an HTTP HEAD request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function head(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('HEAD', $uri, $options);
	}

	/**
	 * Create and send an HTTP PUT request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function put(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('PUT', $uri, $options);
	}

	/**
	 * Create and send an HTTP POST request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function post(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('POST', $uri, $options);
	}

	/**
	 * Create and send an HTTP PATCH request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function patch(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('PATCH', $uri, $options);
	}

	/**
	 * Create and send an HTTP DELETE request.
	 *
	 * @param string $uri URI object or string.
	 * @param HttpOptions|array $options Request options to apply.
	 *
	 * @return HttpResponse
	 */
	public function delete(string $uri, HttpOptions|array $options = []): HttpResponse
	{
		return $this->request('DELETE', $uri, $options);
	}

}