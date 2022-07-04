<?php

namespace EasyHttp\Contracts;

use EasyHttp\Exceptions\WebSocketException;

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
	 * @param string $message
	 * @return void
	 * @throws WebSocketException
	 */
	public function onMessage(string $message): void;

}
