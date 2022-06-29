<?php

namespace EasyHttp\Utils;

use EasyHttp\Contracts\WscCommonsContract;

/**
 * WSConfig class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WSConfig
{

	/**
	 * @var string
	 */
	private string $scheme;

	/**
	 * @var string
	 */
	private string $host;

	/**
	 * @var string
	 */
	private string $user;

	/**
	 * @var string
	 */
	private string $password;

	/**
	 * @var string
	 */
	private string $port;

	/**
	 * @var int
	 */
	private int $timeout = WscCommonsContract::DEFAULT_TIMEOUT;

	/**
	 * @var array
	 */
	private array $headers = [];

	/**
	 * @var int
	 */
	private int $fragmentSize = WscCommonsContract::DEFAULT_FRAGMENT_SIZE;

	/**
	 * @var null|resource
	 */
	private $context;

	/**
	 * @var bool
	 */
	private bool $hasProxy = false;

	/**
	 * @var string
	 */
	private string $proxyIp;

	/**
	 * @var string
	 */
	private string $proxyPort;

	/**
	 * @var string|null
	 */
	private ?string $proxyAuth;

	/**
	 * @var array
	 */
	private array $contextOptions = [];

	/**
	 * @return int
	 */
	public function getTimeout(): int
	{
		return $this->timeout;
	}

	/**
	 * @param int $timeout
	 * @return WSConfig
	 */
	public function setTimeout(int $timeout): WSConfig
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @param array $headers
	 * @return WSConfig
	 */
	public function setHeaders(array $headers): WSConfig
	{
		$this->headers = $headers;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getFragmentSize(): int
	{
		return $this->fragmentSize;
	}

	/**
	 * @param int $fragmentSize
	 * @return WSConfig
	 */
	public function setFragmentSize(int $fragmentSize): WSConfig
	{
		$this->fragmentSize = $fragmentSize;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getContext(): mixed
	{
		return $this->context;
	}

	/**
	 * @param mixed $context
	 * @return WSConfig
	 */
	public function setContext(mixed $context): WSConfig
	{
		$this->context = $context;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getScheme(): string
	{
		return $this->scheme;
	}

	/**
	 * @param string $scheme
	 * @return WSConfig
	 */
	public function setScheme(string $scheme): WSConfig
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @param string $host
	 * @return WSConfig
	 */
	public function setHost(string $host): WSConfig
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser(): string
	{
		return $this->user;
	}

	/**
	 * @param array $urlParts
	 * @return WSConfig
	 */
	public function setUser(array $urlParts): WSConfig
	{
		$this->user = $urlParts['user'] ?? '';
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param array $urlParts
	 * @return WSConfig
	 */
	public function setPassword(array $urlParts): WSConfig
	{
		$this->password = $urlParts['pass'] ?? '';
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPort(): string
	{
		return $this->port;
	}

	/**
	 * @param array $urlParts
	 * @return WSConfig
	 */
	public function setPort(array $urlParts): WSConfig
	{
		$this->port = $urlParts['port'] ?? ($this->scheme === 'wss' ? '443' : '80');
		return $this;
	}

	/**
	 * @return array
	 */
	public function getContextOptions(): array
	{
		return $this->contextOptions;
	}

	/**
	 * @param array $contextOptions
	 * @return WSConfig
	 */
	public function setContextOptions(array $contextOptions): WSConfig
	{
		$this->contextOptions = $contextOptions;
		return $this;
	}

	/**
	 * @param string $ip
	 * @param string $port
	 * @return WSConfig
	 */
	public function setProxy(string $ip, string $port): WSConfig
	{
		$this->hasProxy = true;
		$this->proxyIp = $ip;
		$this->proxyPort = $port;

		return $this;
	}

	/**
	 * Sets auth for proxy
	 *
	 * @param string $userName
	 * @param string $password
	 * @return WSConfig
	 */
	public function setProxyAuth(string $userName, string $password): WSConfig
	{
		$this->proxyAuth = (empty($userName) === false && empty($password) === false) ? base64_encode($userName . ':' . $password) : null;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasProxy(): bool
	{
		return $this->hasProxy;
	}

	/**
	 * @return string|null
	 */
	public function getProxyIp(): ?string
	{
		return $this->proxyIp;
	}

	/**
	 * @return string|null
	 */
	public function getProxyPort(): ?string
	{
		return $this->proxyPort;
	}

	/**
	 * @return string|null
	 */
	public function getProxyAuth(): ?string
	{
		return $this->proxyAuth;
	}

}
