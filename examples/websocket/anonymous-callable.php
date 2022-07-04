<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$SocketClient = new \EasyHttp\WebSocket();

$SocketClient->onOpen = function (\EasyHttp\WebSocket $socket) {
	echo "Connected to server<br>";
	$socket->send("Hello World");
};

$SocketClient->onMessage = function (\EasyHttp\WebSocket $socket, $message) {
	echo $message . "<br>";
	$socket->close();
};

$SocketClient->onClose = function (\EasyHttp\WebSocket $socket, int $closeStatus) {
	echo "Disconnected with status: $closeStatus<br>";
};

$SocketClient->connect('wss://socket.litehex.com/', new \EasyHttp\WebSocketConfig());