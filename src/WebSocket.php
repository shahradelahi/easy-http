<?php

namespace EasyHttp;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Contracts\WscCommonsContract;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\Traits\WSClientTrait;
use EasyHttp\Traits\WSConnectionTrait;

/**
 * WebSocket class
 *
 * @method bool   isConnected()    This method returns true if the connection is established.
 * @method int    getCloseStatus() This method returns the close status after the connection is closed.
 * @method string getSocketUrl()   This method returns the URL of the socket.
 * @method string getLastOpcode()  This method returns the last opcode.
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WebSocket implements WscCommonsContract
{

	use WSClientTrait;
	use WSConnectionTrait;

	/**
	 * App version
	 *
	 * @var string
	 */
	public const VERSION = 'v1.2.0';

	/**
	 * @var resource|bool
	 */
	private $socket;

	/**
	 * @var string
	 */
	private string $lastOpcode;

	/**
	 * @var float|int
	 */
	private float|int $closeStatus;

	/**
	 * @var string|null
	 */
	private ?string $hugePayload;

	/**
	 * @var WebSocketConfig
	 */
	protected WebSocketConfig $config;

	/**
	 * @var string
	 */
	protected string $socketUrl;

	/**
	 * @var array|int[]
	 */
	private static array $opcodes = [
		CommonsContract::EVENT_TYPE_CONTINUATION => 0,
		CommonsContract::EVENT_TYPE_TEXT => 1,
		CommonsContract::EVENT_TYPE_BINARY => 2,
		CommonsContract::EVENT_TYPE_CLOSE => 8,
		CommonsContract::EVENT_TYPE_PING => 9,
		CommonsContract::EVENT_TYPE_PONG => 10,
	];

	/**
	 * Sets parameters for Web Socket Client intercommunication
	 *
	 * @param ?SocketClient $client leave it empty if you want to use default socket client
	 */
	public function __construct(?SocketClient $client = null)
	{
		if ($client instanceof SocketClient) {

			$this->onOpen = function ($socket) use ($client) {
				$client->onOpen($socket);
			};

			$this->onClose = function ($socket, int $closeStatus) use ($client) {
				$client->onClose($socket, $closeStatus);
			};

			$this->onError = function ($socket, WebSocketException $exception) use ($client) {
				$client->onError($socket, $exception);
			};

			$this->onMessage = function ($socket, string $message) use ($client) {
				$client->onMessage($socket, $message);
			};
		}

		$this->config = $config ?? new WebSocketConfig();
	}

	/**
	 * Init a proxy connection
	 *
	 * @return resource|false
	 * @throws \InvalidArgumentException
	 * @throws ConnectionException
	 */
	private function proxy()
	{
		$sock = @stream_socket_client(
			WscCommonsContract::TCP_SCHEME . $this->config->getProxyIp() . ':' . $this->config->getProxyPort(),
			$errno,
			$errstr,
			$this->config->getTimeout(),
			STREAM_CLIENT_CONNECT,
			$this->getStreamContext()
		);

		$write = "CONNECT {$this->config->getProxyIp()}:{$this->config->getProxyPort()} HTTP/1.1\r\n";
		$auth = $this->config->getProxyAuth();

		if ($auth !== NULL) {
			$write .= "Proxy-Authorization: Basic {$auth}\r\n";
		}

		$write .= "\r\n";
		fwrite($sock, $write);
		$resp = fread($sock, 1024);

		if (preg_match(self::PROXY_MATCH_RESP, $resp) === 1) {
			return $sock;
		}

		throw new ConnectionException('Failed to connect to the host via proxy');
	}

	/**
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	private function getStreamContext(): mixed
	{
		if ($this->config->getContext() !== null) {
			// Suppress the error since we'll catch it below
			if (@get_resource_type($this->config->getContext()) === 'stream-context') {
				return $this->config->getContext();
			}

			throw new \InvalidArgumentException(
				'Stream context is invalid',
				CommonsContract::CLIENT_INVALID_STREAM_CONTEXT
			);
		}

		return stream_context_create($this->config->getContextOptions());
	}

	/**
	 * @param mixed $urlParts
	 *
	 * @return string
	 */
	private function getPathWithQuery(mixed $urlParts): string
	{
		$path = $urlParts['path'] ?? '/';
		$query = $urlParts['query'] ?? '';
		$fragment = $urlParts['fragment'] ?? '';
		$pathWithQuery = $path;

		if (!empty($query)) {
			$pathWithQuery .= '?' . $query;
		}

		if (!empty($fragment)) {
			$pathWithQuery .= '#' . $fragment;
		}

		return $pathWithQuery;
	}

	/**
	 * @param string $pathWithQuery
	 * @param array $headers
	 *
	 * @return string
	 */
	private function getHeaders(string $pathWithQuery, array $headers): string
	{
		return 'GET ' . $pathWithQuery . " HTTP/1.1\r\n"
			. implode(
				"\r\n",
				array_map(
					function ($key, $value) {
						return "$key: $value";
					},
					array_keys($headers),
					$headers
				)
			)
			. "\r\n\r\n";
	}

	/**
	 * @param int $timeout
	 * @param null $microSecs
	 *
	 * @return void
	 */
	public function setTimeout(int $timeout, $microSecs = null): void
	{
		$this->config->setTimeout($timeout);
		if ($this->socket && get_resource_type($this->socket) === 'stream') {
			stream_set_timeout($this->socket, $timeout, $microSecs);
		}
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __call(string $name, array $arguments): mixed
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}

		if (method_exists($this, $name)) {
			return call_user_func_array([$this, $name], $arguments);
		}

		if (str_starts_with($name, 'get')) {
			$property = lcfirst(substr($name, 3));

			if (property_exists($this, $property)) {
				return $this->{$property};
			}
		}

		if (str_starts_with($name, 'set')) {
			$property = lcfirst(substr($name, 3));
			if (property_exists($this, $property)) {
				$this->{$property} = $arguments[0];
				return $this;
			}
		}

		throw new \Exception(sprintf("Method '%s' does not exist.", $name));
	}

}