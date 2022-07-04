<?php

namespace EasyHttp;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Contracts\WscCommonsContract;
use EasyHttp\Exceptions\BadOpcodeException;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\Traits\WSClientTrait;

/**
 * WebSocket class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WebSocket implements WscCommonsContract
{

	use WSClientTrait;

	/**
	 * @var callable|null
	 */
	public $onOpen = null;

	/**
	 * @var callable|null
	 */
	public $onClose = null;

	/**
	 * @var callable|null
	 */
	public $onError = null;

	/**
	 * @var callable|null
	 */
	public $onMessage = null;

	/**
	 * @var callable|null
	 */
	public $onWhile = null;

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
	 * @var bool
	 */
	private bool $isConnected = false;

	/**
	 * @var bool
	 */
	private bool $isClosing = false;

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
	 * @var WebSocketConfig
	 */
	protected WebSocketConfig $config;

	/**
	 * @var string
	 */
	protected string $socketUrl;

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
	 * @param string $socketUrl string that represents the URL of the Web Socket server. e.g. ws://localhost:1337 or wss://localhost:1337
	 * @param ?WebSocketConfig $config The configuration for the Web Socket client
	 */
	public function connect(string $socketUrl, ?WebSocketConfig $config = null): void
	{
		try {
			$this->config = $config ?? new WebSocketConfig();
			$this->socketUrl = $socketUrl;
			$urlParts = parse_url($this->socketUrl);

			$this->config->setScheme($urlParts['scheme']);
			$this->config->setHost($urlParts['host']);
			$this->config->setUser($urlParts);
			$this->config->setPassword($urlParts);
			$this->config->setPort($urlParts);

			$pathWithQuery = $this->getPathWithQuery($urlParts);
			$hostUri = $this->getHostUri($this->config);

			$context = $this->getStreamContext();
			if ($this->config->hasProxy()) {
				$this->socket = $this->proxy();
			} else {
				$this->socket = @stream_socket_client(
					$hostUri . ':' . $this->config->getPort(),
					$errno,
					$errstr,
					$this->config->getTimeout(),
					STREAM_CLIENT_CONNECT,
					$context
				);
			}

			if ($this->socket === false) {
				throw new ConnectionException(
					"Could not open socket to \"{$this->config->getHost()}:{$this->config->getPort()}\": $errstr ($errno).",
					CommonsContract::CLIENT_COULD_NOT_OPEN_SOCKET
				);
			}

			stream_set_timeout($this->socket, $this->config->getTimeout());

			$key = $this->generateKey();
			$headers = [
				'Host' => $this->config->getHost() . ':' . $this->config->getPort(),
				'User-Agent' => 'Easy-Http/' . self::VERSION . ' (PHP/' . PHP_VERSION . ')',
				'Connection' => 'Upgrade',
				'Upgrade' => 'WebSocket',
				'Sec-WebSocket-Key' => $key,
				'Sec-Websocket-Version' => '13',
			];

			if ($this->config->getUser() || $this->config->getPassword()) {
				$headers['authorization'] = 'Basic ' . base64_encode($this->config->getUser() . ':' . $this->config->getPassword()) . "\r\n";
			}

			if (!empty($this->config->getHeaders())) {
				$headers = array_merge($headers, $this->config->getHeaders());
			}

			$header = $this->getHeaders($pathWithQuery, $headers);

			$this->write($header);

			$this->validateResponse($this->config, $pathWithQuery, $key);
			$this->isConnected = true;
			$this->onConnection();

		} catch (\Exception $e) {
			if (is_callable($this->onError) && $this->onError) {
				call_user_func($this->onError, $this, new WebSocketException(
					$e->getMessage(),
					$e->getCode(),
					$e
				));
			}
		}
	}

	/**
	 * Reconnect to the Web Socket server
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function reconnect(): void
	{
		if ($this->isConnected) {
			$this->close();
		}

		$this->connect($this->socketUrl, $this->config);
	}

	/**
	 * @return void
	 * @throws WebSocketException|\Exception
	 */
	private function onConnection(): void
	{
		if (is_callable($this->onOpen) && $this->onOpen) {
			call_user_func($this->onOpen, $this);
		}

		while ($this->isConnected()) {
			if (is_callable($this->onWhile) && $this->onWhile) {
				call_user_func($this->onWhile, $this);
			}

			if (is_string(($message = $this->receive()))) {
				if (is_callable($this->onMessage) && $this->onMessage) {
					call_user_func($this->onMessage, $this, $message);
				}
			}
		}

		if (is_callable($this->onClose) && $this->onClose) {
			call_user_func($this->onClose, $this, $this->closeStatus);
		}
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
	 * @return string
	 */
	public function getLastOpcode(): string
	{
		return $this->lastOpcode;
	}

	/**
	 * @return int
	 */
	public function getCloseStatus(): int
	{
		return $this->closeStatus;
	}

	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->isConnected;
	}

	/**
	 * @param int $timeout
	 * @param null $microSecs
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
	 * Sends message to opened socket connection client->server
	 *
	 * @param $payload
	 * @param string $opcode
	 * @throws \Exception
	 */
	public function send($payload, string $opcode = CommonsContract::EVENT_TYPE_TEXT): void
	{
		if (!$this->isConnected) {
			throw new \Exception(
				"Can't send message. Connection is not established.",
				CommonsContract::CLIENT_CONNECTION_NOT_ESTABLISHED
			);
		}

		if (array_key_exists($opcode, self::$opcodes) === false) {
			throw new BadOpcodeException(
				sprintf("Bad opcode '%s'.  Try 'text' or 'binary'.", $opcode),
				CommonsContract::CLIENT_BAD_OPCODE
			);
		}

		$payloadLength = strlen($payload);
		$fragmentCursor = 0;

		while ($payloadLength > $fragmentCursor) {
			$subPayload = substr($payload, $fragmentCursor, $this->config->getFragmentSize());
			$fragmentCursor += $this->config->getFragmentSize();
			$final = $payloadLength <= $fragmentCursor;
			$this->sendFragment($final, $subPayload, $opcode, true);
			$opcode = 'continuation';
		}
	}

	/**
	 * Receives message client<-server
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	public function receive(): string|null
	{
		if (!$this->isConnected && $this->isClosing === false) {
			throw new WebSocketException(
				"Your unexpectedly disconnected from the server",
				CommonsContract::CLIENT_CONNECTION_NOT_ESTABLISHED
			);
		}

		$this->hugePayload = '';

		return $this->receiveFragment();
	}

	/**
	 * Tell the socket to close.
	 *
	 * @param integer $status http://tools.ietf.org/html/rfc6455#section-7.4
	 * @param string $message A closing message, max 125 bytes.
	 * @return bool|null|string
	 * @throws \Exception
	 */
	public function close(int $status = 1000, string $message = 'ttfn'): bool|null|string
	{
		$statusBin = sprintf('%016b', $status);
		$statusStr = '';

		foreach (str_split($statusBin, 8) as $binstr) {
			$statusStr .= chr(bindec($binstr));
		}

		$this->send($statusStr . $message, CommonsContract::EVENT_TYPE_CLOSE);
		$this->isClosing = true;

		return $this->receive(); // Receiving a close frame will close the socket now.
	}

	/**
	 * @return string
	 */
	public function getSocketUrl(): string
	{
		return $this->socketUrl;
	}

	/**
	 * @param int $len
	 * @return string|null
	 * @throws ConnectionException
	 */
	protected function read(int $len): string|null
	{
		if ($this->socket && $this->isConnected()) {
			return Middleware::stream_read($this->socket, $len);
		}

		return null;
	}

	/**
	 * @param string $data
	 * @throws ConnectionException
	 */
	protected function write(string $data): void
	{
		Middleware::stream_write($this->socket, $data);
	}

	/**
	 * Helper to convert a binary to a string of '0' and '1'.
	 *
	 * @param string $string
	 * @return string
	 */
	protected static function sprintB(string $string): string
	{
		$return = '';
		$strLen = strlen($string);
		for ($i = 0; $i < $strLen; $i++) {
			$return .= sprintf('%08b', ord($string[$i]));
		}

		return $return;
	}

}