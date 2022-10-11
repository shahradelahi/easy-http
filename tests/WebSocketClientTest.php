<?php declare(strict_types=1);

namespace EasyHttp\Tests;

use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\SocketClient;
use EasyHttp\Utils\Toolkit;
use EasyHttp\WebSocket;
use EasyHttp\WebSocketConfig;

class WebSocketClientTest extends \PHPUnit\Framework\TestCase
{

    private string $url = "wss://socket.litehex.us/";

    public function testIsConnected(): void
    {
        $ws = new WebSocket();

        $ws->on('open', function (WebSocket $socket) {
            $this->assertTrue($socket->isConnected());
            echo "Connected to" . PHP_EOL;
            $socket->send('Hello World');
        });

        $ws->on('message', function (WebSocket $socket, $message) {
            $this->assertEquals('Hello World', $message);
            $socket->close();
            echo $message;
        });

        $ws->connect($this->url, (new WebSocketConfig())->setHeaders([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]));
    }

    public function testHeadersAreWorking(): void
    {
        $randomString = Toolkit::randomString(32);

        $ws = new WebSocket();

        $ws->on('open', function ($socket) {
            $socket->send('Headers');
        });

        $ws->on('message', function ($socket, $message) use ($randomString) {
            $headers = json_decode($message, true);
            $this->assertEquals($randomString, $headers['x-subscribe-with']);
            $socket->close();
        });

        $ws->connect($this->url, (new WebSocketConfig())->setHeaders([
            'X-Subscribe-With' => $randomString,
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCanSendLargePayload(): void
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

        $ws = new WebSocket();

        $ws->on('open', function (WebSocket $socket) use ($sendMe) {
            $socket->send(json_encode($sendMe));
        });

        $ws->on('message', function (WebSocket $socket, $message) use ($sendMe) {
            $this->assertEquals('Message: ' . json_encode($sendMe), $message);
            $socket->close();
        });

        $ws->connect($this->url, (new WebSocketConfig())->setHeaders([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testWithClient(): void
    {
        $ws = new WebSocket(new class extends SocketClient {

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

        $ws->connect($this->url, (new WebSocketConfig())->setHeaders([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]));

        $this->assertFalse($ws->isConnected());
    }

}