<?php declare(strict_types=1);

namespace EasyHttp\Contracts;

use EasyHttp\WebSocket;

/**
 * LiveEventContract class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
interface LiveEventContract
{

    /**
     * @param WebSocket $socket
     * @return void
     */
    public function onPing(WebSocket $socket): void;

    /**
     * @param WebSocket $socket
     * @return void
     */
    public function onPong(WebSocket $socket): void;

}
