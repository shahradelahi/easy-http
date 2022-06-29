<?php

namespace EasyHttp\Contracts;

use EasyHttp\Exceptions\WebSocketException;

/**
 * MessageContract class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
interface MessageContract
{

	/**
	 * @param ConnectionContract $recv
	 * @param mixed $msg
	 * @return mixed
	 * @throws WebSocketException
	 */
	public function onMessage(ConnectionContract $recv, mixed $msg): mixed;

}
