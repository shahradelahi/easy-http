<?php declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$SocketClient = new \EasyHttp\WebSocket();

$SocketClient->on('open', function (\EasyHttp\WebSocket $socket) {
    echo "Connected to server<br>";
    $socket->send("Hello World");
});

$SocketClient->on('message', function (\EasyHttp\WebSocket $socket, $message) {
    echo $message . "<br>";
    $socket->close();
});

$SocketClient->on('close', function (\EasyHttp\WebSocket $socket, int $closeStatus) {
    echo "Disconnected with status: $closeStatus<br>";
});

$SocketClient->connect('wss://socket.litehex.com/', new \EasyHttp\WebSocketConfig());
