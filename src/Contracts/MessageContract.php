<?php

namespace EasyHttp\Contracts;

use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\WebSocket;

/**
 * MessageContract class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
interface MessageContract
{

	/**
	 * @param WebSocket $socket
	 * @param string $message
	 * @return void
	 */
	public function onMessage(WebSocket $socket, string $message): void;

}
