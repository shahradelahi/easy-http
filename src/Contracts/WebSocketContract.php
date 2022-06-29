<?php

namespace EasyHttp\Contracts;

use EasyHttp\Exceptions\WebSocketException;

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
	 * @param ConnectionContract $conn
	 * @return mixed
	 */
	public function onOpen(ConnectionContract $conn): mixed;

	/**
	 * @param ConnectionContract $conn
	 * @return mixed
	 * @throws WebSocketException
	 */
	public function onClose(ConnectionContract $conn): mixed;

	/**
	 * @param ConnectionContract $conn
	 * @param WebSocketException $ex
	 * @return mixed
	 */
	public function onError(ConnectionContract $conn, WebSocketException $ex): mixed;

}
