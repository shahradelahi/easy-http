<?php

namespace EasyHttp\Traits;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Exceptions\BadUriException;
use EasyHttp\Exceptions\ConnectionException;
use EasyHttp\Middleware;
use EasyHttp\Utils\Toolkit;
use EasyHttp\WebSocketConfig;

/**
 * WSClientTrait class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
trait WSClientTrait
{

	/**
	 * Validates whether server sent valid upgrade response
	 *
	 * @param WebSocketConfig $config
	 * @param string $pathWithQuery
	 * @param string $key
	 * @throws ConnectionException
	 */
	private function validateResponse(WebSocketConfig $config, string $pathWithQuery, string $key): void
	{
		$response = stream_get_line($this->socket, self::DEFAULT_RESPONSE_HEADER, "\r\n\r\n");
		if (!preg_match(self::SEC_WEBSOCKET_ACCEPT_PTTRN, $response, $matches)) {
			$address = $config->getScheme() . '://' . $config->getHost() . ':' . $config->getPort() . $pathWithQuery;
			throw new ConnectionException(
				"Connection to '{$address}' failed: Server sent invalid upgrade response:\n"
				. $response, CommonsContract::CLIENT_INVALID_UPGRADE_RESPONSE
			);
		}

		$keyAccept = trim($matches[1]);
		$expectedResponse = base64_encode(pack('H*', sha1($key . self::SERVER_KEY_ACCEPT)));
		if ($keyAccept !== $expectedResponse) {
			throw new ConnectionException(
				'Server sent bad upgrade response.',
				CommonsContract::CLIENT_INVALID_UPGRADE_RESPONSE
			);
		}
	}

	/**
	 *  Gets host uri based on protocol
	 *
	 * @param WebSocketConfig $config
	 * @return string
	 * @throws BadUriException
	 */
	private function getHostUri(WebSocketConfig $config): string
	{
		if (in_array($config->getScheme(), ['ws', 'wss'], true) === false) {
			throw new BadUriException(
				"Url should have scheme ws or wss, not '{$config->getScheme()}' from URI '$this->socketUrl' .",
				CommonsContract::CLIENT_INCORRECT_SCHEME
			);
		}

		return ($config->getScheme() === 'wss' ? 'ssl' : 'tcp') . '://' . $config->getHost();
	}

	/**
	 * @param string $data
	 * @return float|int
	 * @throws ConnectionException
	 */
	private function getPayloadLength(string $data): float|int
	{
		$payloadLength = (int)ord($data[1]) & self::MASK_127; // Bits 1-7 in byte 1
		if ($payloadLength > self::MASK_125) {
			if ($payloadLength === self::MASK_126) {
				$data = $this->read(2); // 126: Payload is a 16-bit unsigned int
			} else {
				$data = $this->read(8); // 127: Payload is a 64-bit unsigned int
			}
			$payloadLength = bindec(Toolkit::sprintB($data));
		}

		return $payloadLength;
	}

	/**
	 * @param string $data
	 * @param int $payloadLength
	 * @return string
	 * @throws ConnectionException
	 */
	private function getPayloadData(string $data, int $payloadLength): string
	{
		// Masking?
		$mask = (bool)(ord($data[1]) >> 7);  // Bit 0 in byte 1
		$payload = '';
		$maskingKey = '';

		// Get masking key.
		if ($mask) {
			$maskingKey = $this->read(4);
		}

		// Get the actual payload, if any (might not be for e.g. close frames.
		if ($payloadLength > 0) {
			$data = $this->read($payloadLength);

			if ($mask) {
				// Unmask payload.
				for ($i = 0; $i < $payloadLength; $i++) {
					$payload .= ($data[$i] ^ $maskingKey[$i % 4]);
				}
			} else {
				$payload = $data;
			}
		}

		return $payload;
	}

	/**
	 * @return string|null
	 * @throws \Exception
	 */
	protected function receiveFragment(): string|null
	{
		$data = $this->read(2);
		if (is_string($data) === false) {
			return null;
		}

		$final = (bool)(ord($data[0]) & 1 << 7);

		$opcodeInt = ord($data[0]) & 31;
		$opcodeInts = array_flip(self::$opcodes);
		if (!array_key_exists($opcodeInt, $opcodeInts)) {
			throw new ConnectionException(
				"Bad opcode in websocket frame: $opcodeInt",
				CommonsContract::CLIENT_BAD_OPCODE
			);
		}

		$opcode = $opcodeInts[$opcodeInt];

		if ($opcode !== 'continuation') {
			$this->lastOpcode = $opcode;
		}

		$payloadLength = $this->getPayloadLength($data);
		$payload = $this->getPayloadData($data, $payloadLength);

		if ($opcode === CommonsContract::EVENT_TYPE_CLOSE) {
			if ($payloadLength >= 2) {
				$statusBin = $payload[0] . $payload[1];
				$status = bindec(sprintf('%08b%08b', ord($payload[0]), ord($payload[1])));
				$this->closeStatus = $status;
				$payload = substr($payload, 2);

				if (!$this->isClosing) {
					$this->send($statusBin . 'Close acknowledged: ' . $status,
						CommonsContract::EVENT_TYPE_CLOSE); // Respond.
				}
			}

			if ($this->isClosing) {
				$this->isClosing = false; // A close response, all done.
			}

			fclose($this->socket);
			$this->isConnected = false;
		}

		if (!$final) {
			$this->hugePayload .= $payload;

			return null;
		}

		if ($this->hugePayload) {
			$payload = $this->hugePayload .= $payload;
			$this->hugePayload = null;
		}

		return $payload;
	}

	/**
	 * @param $final
	 * @param $payload
	 * @param $opcode
	 * @param $masked
	 * @throws \Exception
	 */
	protected function sendFragment($final, $payload, $opcode, $masked): void
	{
		// Binary string for header.
		$frameHeadBin = '';
		// Write FIN, final fragment bit.
		$frameHeadBin .= (bool)$final ? '1' : '0';
		// RSV 1, 2, & 3 false and unused.
		$frameHeadBin .= '000';
		// Opcode rest of the byte.
		$frameHeadBin .= sprintf('%04b', self::$opcodes[$opcode]);
		// Use masking?
		$frameHeadBin .= $masked ? '1' : '0';

		// 7 bits of payload length...
		$payloadLen = strlen($payload);
		if ($payloadLen > self::MAX_BYTES_READ) {
			$frameHeadBin .= decbin(self::MASK_127);
			$frameHeadBin .= sprintf('%064b', $payloadLen);
		} else if ($payloadLen > self::MASK_125) {
			$frameHeadBin .= decbin(self::MASK_126);
			$frameHeadBin .= sprintf('%016b', $payloadLen);
		} else {
			$frameHeadBin .= sprintf('%07b', $payloadLen);
		}

		$frame = '';

		// Write frame head to frame.
		foreach (str_split($frameHeadBin, 8) as $binstr) {
			$frame .= chr(bindec($binstr));
		}
		// Handle masking
		if ($masked) {
			// generate a random mask:
			$mask = '';
			for ($i = 0; $i < 4; $i++) {
				$mask .= chr(random_int(0, 255));
			}
			$frame .= $mask;
		}

		// Append payload to frame:
		for ($i = 0; $i < $payloadLen; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		$this->write($frame);
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
	 * @return void
	 * @throws ConnectionException
	 */
	protected function write(string $data): void
	{
		Middleware::stream_write($this->socket, $data);
	}

}