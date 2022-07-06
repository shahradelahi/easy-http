<?php

namespace EasyHttp\Traits;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Exceptions\BadOpcodeException;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\WebSocketConfig;

/**
 * WSConnectionTrait class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
trait WSConnectionTrait
{

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
	 * @var bool
	 */
	private bool $isConnected = false;

	/**
	 * @var bool
	 */
	private bool $isClosing = false;

	/**
	 * Default headers
	 *
	 * @var array
	 */
	private array $defaultHeaders = [
		'Connection' => 'Upgrade',
		'Upgrade' => 'WebSocket',
		'Sec-Websocket-Version' => '13',
	];

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
			$headers = array_merge($this->defaultHeaders, [
				'Host' => $this->config->getHost() . ':' . $this->config->getPort(),
				'User-Agent' => 'Easy-Http/' . self::VERSION . ' (PHP/' . PHP_VERSION . ')',
				'Sec-WebSocket-Key' => $key,
			]);

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
			$this->whileIsConnected();

		} catch (\Exception $e) {
			$this->safeCall($this->onError, $this, new WebSocketException(
				$e->getMessage(),
				$e->getCode(),
				$e->getPrevious()
			));
		}
	}

	/**
	 * Reconnect to the Web Socket server
	 *
	 * @return void
	 * @throws \Exception
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
	private function whileIsConnected(): void
	{
		$this->safeCall($this->onOpen, $this);

		while ($this->isConnected() && $this->isClosing === false) {
			$this->safeCall($this->onWhile, $this);

			if (is_string(($message = $this->receive()))) {
				$this->safeCall($this->onMessage, $this, $message);
			}
		}

		$this->safeCall($this->onClose, $this, $this->closeStatus);
	}

	/**
	 * Execute events with safety of exceptions
	 *
	 * @param callable|null $callback
	 * @param mixed ...$args
	 * @return void
	 */
	private function safeCall(?callable $callback, ...$args): void
	{
		if (is_callable($callback) && $callback) {
			call_user_func($callback, ...$args);
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
	 * @param integer $status https://github.com/Luka967/websocket-close-codes
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
		$this->closeStatus = $status;
		$this->isClosing = true;

		return $this->receive(); // Receiving a close frame will close the socket now.
	}

}