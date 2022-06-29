<?php

namespace EasyHttp;

use EasyHttp\Contracts\ConnectionContract;
use EasyHttp\Contracts\MessageContract;
use EasyHttp\Contracts\WebSocketContract;
use EasyHttp\Exceptions\WebSocketException;

/**
 * WebSocketClient class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
abstract class WebSocketClient implements WebSocketContract, MessageContract
{

	/**
	 * @var array
	 */
	public array $pathParams = [];

	/**
	 * You may want to implement these methods to bring ping/pong events
	 *
	 * @param ConnectionContract $conn
	 * @param string $msg
	 * @throws WebSocketException
	 */
	abstract public function onPing(ConnectionContract $conn, string $msg);

	/**
	 * @param ConnectionContract $conn
	 * @param mixed $msg
	 * @throws WebSocketException
	 */
	abstract public function onPong(ConnectionContract $conn, mixed $msg);

}
