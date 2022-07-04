<?php

namespace EasyHttp\Contracts;

use EasyHttp\Exceptions\WebSocketException;
use EasyHttp\WebSocket;

/**
 * WebSocketContract class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
interface WebSocketContract
{

	/**
	 * @param WebSocket $socket
	 * @return void
	 */
	public function onOpen(WebSocket $socket): void;

	/**
	 * @param WebSocket $socket
	 * @param int $closeStatus
	 * @return void
	 */
	public function onClose(WebSocket $socket, int $closeStatus): void;

	/**
	 * @param WebSocket $socket
	 * @param WebSocketException $exception
	 * @return void
	 */
	public function onError(WebSocket $socket, WebSocketException $exception): void;

}
