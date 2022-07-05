<?php

namespace EasyHttp\Test;

use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\SocketClient;
use EasyHttp\Utils\Toolkit;
use EasyHttp\WebSocket;
use EasyHttp\WebSocketConfig;

class WebSocketClientTest extends \PHPUnit\Framework\TestCase
{

	private const WS_SCHEME = 'wss://';
	private const WS_HOST = 'socket.litehex.com';
	private const WS_PATH = '/echo';

	private string $url = self::WS_SCHEME . self::WS_HOST . self::WS_PATH;

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function test_is_connected(): void
	{
		$socket = new WebSocket();

		$socket->onOpen = function (WebSocket $socket) {
			$socket->send('Hello World');
		};

		$socket->onMessage = function (WebSocket $socket, $message) {
			echo $message;
			$this->assertEquals('Hello World', $message);
			$socket->close();
		};

		$socket->onError = function (WebSocket $socket, WebSocketException $e) {
			echo $e->getMessage();
		};

		$socket->connect($this->url);
	}

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function test_headers_are_working(): void
	{
		$randomString = Toolkit::randomString(32);
		$config = (new WebSocketConfig())->setHeaders([
			'X-Subscribe-With' => $randomString,
		]);

		$socket = new WebSocket();

		$socket->onOpen = function ($socket) {
			$socket->send('Headers');
		};

		$socket->onMessage = function ($socket, $message) use ($randomString) {
			$headers = json_decode($message, true);
			$this->assertEquals($randomString, $headers['x-subscribe-with']);
			$socket->close();
		};

		$socket->connect($this->url, $config);
	}

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function test_can_send_large_payload(): void
	{
		$sendMe = [];
		foreach (range(0, 100) as $i) {
			$sendMe[] = [
				'id' => $i,
				'name' => "Madame Uppercut",
				'age' => rand(1, 100),
				'secretIdentity' => "Jane Wilson",
				'powers' => [
					"million tonne punch",
					"damage resistance",
					"superhuman reflexes"
				]
			];
		}

		$socket = new WebSocket();

		$socket->onOpen = function (WebSocket $socket) use ($sendMe) {
			$socket->send(json_encode($sendMe));
		};

		$socket->onMessage = function (WebSocket $socket, $message) use ($sendMe) {
			$this->assertEquals('Message: ' . json_encode($sendMe), $message);
			$socket->close();
		};

		$socket->connect($this->url, new WebSocketConfig());
	}

	/**
	 * @return void
	 * @throws \Exception
	 */
	public function test_with_client(): void
	{
		$socket = new WebSocket(new class extends SocketClient {

			public function onOpen(WebSocket $socket): void
			{
				$socket->send('Hello World');
			}

			public function onClose(WebSocket $socket, int $closeStatus): void
			{
				echo "Closed with status: $closeStatus";
			}

			public function onError(WebSocket $socket, WebSocketException $exception): void
			{
				echo sprintf(
					"Error: %s\nFile: %s:%s\n",
					$exception->getMessage(),
					$exception->getFile(),
					$exception->getLine()
				);
			}

			public function onMessage(WebSocket $socket, string $message): void
			{
				$socket->close();
			}

		});
		$socket->connect($this->url, new WebSocketConfig());
		$this->assertFalse($socket->isConnected());
	}

}