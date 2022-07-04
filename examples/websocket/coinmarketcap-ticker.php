<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\WebSocket;
use EasyHttp\WebSocketConfig;


$close_time = time() + 10;
$SocketClient = new WebSocket();

$SocketClient->onWhile = function (WebSocket $socket) use ($close_time) {
	if (time() >= $close_time) {
		$socket->close();
	}
};

$SocketClient->onOpen = function (WebSocket $socket) {
	echo sprintf(
		'<pre><b>%s</b>: Connected to %s</pre><br/>',
		date('Y-m-d H:i:s'),
		$socket->getSocketUrl()
	);

	$socket->send(json_encode([
		'method' => 'subscribe',
		'id' => 'price',
		'data' => [
			'cryptoIds' => [1, 1027, 825, 3408, 1839, 4687, 52, 2010, 5426],
			'index' => 'detail'
		]
	]));
};

$SocketClient->onClose = function (WebSocket $socket, int $closeStatus) {
	echo sprintf(
		'<pre><b>%s</b>: Disconnected with status: %s</pre><br/>',
		date('Y-m-d H:i:s'),
		$closeStatus
	);
};

$SocketClient->onMessage = function (WebSocket $socket, string $message) {
	$data = json_decode($message, true);
	if (isset($data['id']) && $data['id'] == "price") {
		echo sprintf(
			'<pre><b>%s</b>: %s</pre><br/>',
			date('Y-m-d H:i:s'),
			$message
		);
	}
};

$SocketClient->onError = function (WebSocket $socket, WebSocketException $exception) {
	echo sprintf(
		"<pre>%s: Error: %s<br>File: %s:%s<br></pre><br>",
		date('Y-m-d H:i:s'),
		$exception->getMessage(),
		$exception->getFile(),
		$exception->getLine()
	);
};

$SocketClient->connect(
	'wss://stream.coinmarketcap.com/price/latest',
	(new WebSocketConfig())->setFragmentSize(8096)->setTimeout(15)
);