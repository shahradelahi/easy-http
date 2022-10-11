<?php declare(strict_types=1);

namespace EasyHttp;

use EasyHttp\Contracts\LiveEventContract;
use EasyHttp\Contracts\MessageContract;
use EasyHttp\Contracts\WebSocketContract;

/**
 * SocketClient class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
abstract class SocketClient implements WebSocketContract, MessageContract, LiveEventContract
{

    public function onPing(WebSocket $socket): void
    {
        $socket->send(time() . '~PONG');
    }

    public function onPong(WebSocket $socket): void
    {
        $socket->send(time() . '~PING');
    }

    public function onMeantime(WebSocket $socket): void
    {
        return;
    }

}
