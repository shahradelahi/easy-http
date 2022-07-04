<?php

namespace EasyHttp\Test;

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
	 * @throws \Exception
	 */
	public function test_is_connected()
	{
		$socket = new WebSocket($this->url, new WebSocketConfig());

		$socket->send('Hello World');
		$received = $socket->receive();
		$socket->close();

		$this->assertEquals('Message: Hello World', $received);
	}

	/**
	 * @throws \Exception
	 */
	public function test_headers_are_working()
	{
		$randomString = Toolkit::randomString(32);
		$config = (new WebSocketConfig())->setHeaders([
			'X-Subscribe-With' => $randomString,
		]);

		$socket = new WebSocket($this->url, $config);

		$socket->send('Headers');
		$received = $socket->receive();
		$socket->close();

		$headers = json_decode($received, true);
		$this->assertEquals($randomString, $headers['x-subscribe-with']);
	}

	/**
	 * @throws \Exception
	 */
	public function test_can_send_large_payload()
	{
		$message = [];
		foreach (range(0, 100) as $i) {
			$message[] = [
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

		$socket = new WebSocket($this->url, new WebSocketConfig());
		$socket->send(json_encode($message));

		$received = $socket->receive();
		$this->assertEquals('Message: ' . json_encode($message), $received);
	}

}