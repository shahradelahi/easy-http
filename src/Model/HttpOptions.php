<?php

namespace EasyHttp\Model;

/**
 * HttpOptions class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 *
 * @method array       getHeaders()         Get the header
 * @method array       getCookie()          Get the cookie
 * @method string|null getBody()            Get the body
 * @method int         getTimeout()         Get the timeout
 * @method array       getMultipart()       Get the multipart
 * @method ProxyServer getProxy()           Get the proxy
 * @method array       getQuery()           Get the query
 * @method array       getCurlOptions()     Get the curl options
 *
 * @method $this setHeaders(array $headers)         Set the header
 * @method $this setCookie(array $cookie)           Set the cookie
 * @method $this setQuery(array $query)             Set the query
 * @method $this setTimeout(int $timeout)           Set the timeout
 * @method $this setMultipart(array $multipart)     Set the multipart
 */
class HttpOptions
{

	/**
	 * An array of HTTP header fields to set, in the format array('<em>Content-type: text/plain</em>', '<em>Content-length: 100</em>')
	 *
	 * @var array
	 */
	private array $headers = [];

	/**
	 * An array of cookies to set, in the format array('name' => 'value', 'name2' => 'value2')
	 *
	 * @var array
	 */
	private array $cookie = [];

	/**
	 * An array of query data (e.g., array('id' => '123', 'name' => 'John')) for use in the query string part of the URI (e.g., http://example.com/index.php?id=123&name=John)
	 *
	 * @var array
	 */
	private array $query = [];

	/**
	 * The body of the HTTP request
	 *
	 * @var ?string
	 */
	private ?string $body = null;

	/**
	 * The maximum number of seconds to allow cURL functions to execute
	 *
	 * @var int
	 */
	private int $timeout = 30;

	/**
	 * An array of multipart data (e.g., array('name', 'contents', 'size')), for use in the multipart/form-data part of the request body
	 *
	 * @var array
	 */
	private array $multipart = [];

	/**
	 * An array of cURL options
	 *
	 * @var array
	 */
	private array $curlOptions = [];

	/**
	 * The proxy server to use
	 *
	 * @var ?ProxyServer
	 */
	private ?ProxyServer $proxy = null;

	/**
	 * Http Options constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$this->setOptions($options);
	}

	/**
	 * Returns the class as an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$result = [];
		foreach (get_object_vars($this) as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	/**
	 * Set Options
	 *
	 * @param array $options
	 * @return void
	 */
	public function setOptions(array $options): void
	{
		foreach ($options as $key => $value) {
			if (method_exists($this, 'set' . ucfirst($key))) {
				if ($value !== null) {
					$this->{'set' . ucfirst($key)}($value);
				}
			} else {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				} else {
					throw new \InvalidArgumentException("Invalid option: $key");
				}
			}
		}
	}

	/**
	 * Set Body of Http request
	 *
	 * @param ?string|array $body The body of the request - On array it will be converted to json
	 * @return void
	 */
	public function setBody(string|array|null $body): void
	{
		if (is_array($body)) {
			$this->body = json_encode($body);
			$this->headers['Content-Type'] = 'application/json';
		} else {
			$this->body = $body;
		}
	}

	/**
	 * Set proxy server
	 *
	 * @param array $proxy ["host", "port", "user", "pass"]
	 * @return void
	 */
	public function setProxy(array $proxy): void
	{
		$this->proxy = (new ProxyServer())->setProxy($proxy);
	}

	/**
	 * Generate URL-encoded query string.
	 *
	 * @return string
	 */
	public function getQueryString(): string
	{
		return http_build_query($this->query);
	}

	/**
	 * Set Curl Options
	 *
	 * @param array $options [{"CURLOPT_*": "value"}, ...]
	 * @return void
	 */
	public function setCurlOptions(array $options): void
	{
		if (count($options) > 0) {
			foreach ($options as $option => $value) {
				$this->curlOptions[$option] = $value;
			}
		}
	}

	/**
	 * Add Multipart Data
	 *
	 * @param array $multipart [{"name", "path"}, ...]
	 * @return void
	 */
	public function addMultiPart(array $multipart): void
	{
		$this->multipart[] = $multipart;
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments): mixed
	{
		if (method_exists($this, $name)) {
			return $this->{$name}(...$arguments);
		}

		if (property_exists($this, $name)) {
			return $this->{$name};
		}

		if (str_starts_with($name, 'set')) {
			$property = lcfirst(substr($name, 3));
			if (property_exists($this, $property)) {
				$this->{$property} = $arguments[0];
				return $this;
			}
		}

		if (str_starts_with($name, 'get')) {
			$property = lcfirst(substr($name, 3));
			if (property_exists($this, $property)) {
				return $this->{$property};
			}
		}

		throw new \BadMethodCallException("Method $name does not exist");
	}

}