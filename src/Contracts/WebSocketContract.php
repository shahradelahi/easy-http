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
	 * @return void
	 */
	public function onOpen(): void;

	/**
	 * @param int $closeStatus
	 * @return void
	 */
	public function onClose(int $closeStatus): void;

	/**
	 * @param WebSocketException $exception
	 * @return void
	 */
	public function onError(WebSocketException $exception): void;

}
