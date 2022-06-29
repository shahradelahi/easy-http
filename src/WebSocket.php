<?php

namespace EasyHttp;

use EasyHttp\Utils\WSConfig;
use EasyHttp\Utils\WscMain;
use Exception;

/**
 * WebSocket class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Arthur Kushman (https://github.com/arthurkushman)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class WebSocket extends WscMain
{

	/**
	 * Sets parameters for Web Socket Client intercommunication
	 *
	 * @param string $url string representation of a socket utf, ex.: tcp://www.example.com:8000 or udp://example.com:13
	 * @param WSConfig $config Client configuration settings e.g.: connection - timeout, ssl options, fragment message size to send etc.
	 * @throws Exception
	 */
	public function __construct(string $url, WSConfig $config)
	{
		$this->socketUrl = $url;
		$this->connect($config);
	}

}
