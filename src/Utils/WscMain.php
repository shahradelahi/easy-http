<?php

namespace EasyHttp\Utils;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Contracts\WscCommonsContract;
use EasyHttp\Exceptions\BadOpcodeException;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Traits\WSClientTrait;
use InvalidArgumentException;

/**
 * WscMain class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WscMain implements WscCommonsContract
{

	use WSClientTrait;

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
	 * @var string
	 */
	protected string $socketUrl = '';

	/**
	 * @var WSConfig
	 */
	protected WSConfig $config;

	/**
	 * @param WSConfig $config
	 * @throws \Exception
	 */
	protected function connect(WSConfig $config): void
	{
		$this->config = $config;
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
			'User-Agent' => 'websocket-client-php',
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
	}

	/**
	 * Init a proxy connection
	 *
	 * @return resource|false
	 * @throws InvalidArgumentException
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
		$path = isset($urlParts['path']) ? $urlParts['path'] : '/';
		$query = isset($urlParts['query']) ? $urlParts['query'] : '';
		$fragment = isset($urlParts['fragment']) ? $urlParts['fragment'] : '';
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
	 * @return WscMain
	 */
	public function setTimeout(int $timeout, $microSecs = null): WscMain
	{
		$this->config->setTimeout($timeout);
		if ($this->socket && get_resource_type($this->socket) === 'stream') {
			stream_set_timeout($this->socket, $timeout, $microSecs);
		}

		return $this;
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
			$this->connect(new WSConfig());
		}

		if (array_key_exists($opcode, self::$opcodes) === false) {
			throw new BadOpcodeException(
				"Bad opcode '$opcode'.  Try 'text' or 'binary'.",
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
	 * @return null|string
	 * @throws \Exception
	 */
	public function receive(): ?string
	{
		if (!$this->isConnected) {
			$this->connect(new WSConfig());
		}

		$this->hugePayload = '';

		$response = null;
		while ($response === null) {
			$response = $this->receiveFragment();
		}

		return $response;
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
	 * @param string $data
	 * @throws ConnectionException
	 */
	protected function write(string $data): void
	{
		$written = fwrite($this->socket, $data);

		if ($written < strlen($data)) {
			throw new ConnectionException(
				"Could only write $written out of " . strlen($data) . ' bytes.',
				CommonsContract::CLIENT_COULD_ONLY_WRITE_LESS
			);
		}
	}

	/**
	 * @param int $len
	 * @return string
	 * @throws ConnectionException
	 */
	protected function read(int $len): string
	{
		$data = '';
		while (($dataLen = strlen($data)) < $len) {
			$buff = fread($this->socket, $len - $dataLen);

			if ($buff === false) {
				$metadata = stream_get_meta_data($this->socket);
				throw new ConnectionException(
					'Broken frame, read ' . strlen($data) . ' of stated '
					. $len . ' bytes.  Stream state: '
					. json_encode($metadata),
					CommonsContract::CLIENT_BROKEN_FRAME
				);
			}

			if ($buff === '') {
				$metadata = stream_get_meta_data($this->socket);
				throw new ConnectionException(
					'Empty read; connection dead?  Stream state: ' . json_encode($metadata),
					CommonsContract::CLIENT_EMPTY_READ
				);
			}
			$data .= $buff;
		}

		return $data;
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

	/**
	 * Sec-WebSocket-Key generator
	 *
	 * @return string   the 16 character length key
	 * @throws \Exception
	 */
	private function generateKey(): string
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$&/()=[]{}0123456789';
		$key = '';
		$chLen = strlen($chars);
		for ($i = 0; $i < self::KEY_GEN_LENGTH; $i++) {
			$key .= $chars[random_int(0, $chLen - 1)];
		}

		return base64_encode($key);
	}

}
