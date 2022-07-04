<?php

namespace EasyHttp;

use EasyHttp\Contracts\CommonsContract;
use EasyHttp\Contracts\MessageContract;
use EasyHttp\Contracts\WebSocketContract;

/**
 * SocketClient class
 *
 * @method void send($payload, string $opcode = CommonsContract::EVENT_TYPE_TEXT)
 * @method bool|null|string close(int $status = 1000, string $message = 'ttfn')
 * @method void onOpen()
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
abstract class SocketClient implements WebSocketContract, MessageContract
{

	/**
	 * @var WebSocket
	 */
	protected WebSocket $websocket;

	/**
	 * @param WebSocket $websocket
	 * @return void
	 */
	protected function setConnection(WebSocket $websocket): void
	{
		$this->websocket = $websocket;
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments): mixed
	{
		if (method_exists($this, $name)) {
			return call_user_func_array([$this, $name], $arguments);
		}

		if (method_exists($this->websocket, $name)) {
			return call_user_func_array([$this->websocket, $name], $arguments);
		}

		throw new \BadMethodCallException(sprintf(
			'Method `%s` does not exist at `%s`', $name, get_class($this)
		));
	}

}
