<?php

namespace EasyHttp;

use EasyHttp\Utils\Toolkit;

/**
 * Loop class
 *
 * @link    https://github.com/shahradelahi/easy-http
 * @author  Shahrad Elahi (https://github.com/shahradelahi)
 * @license https://github.com/shahradelahi/easy-http/blob/master/LICENSE (MIT License)
 */
class Loop
{

	/**
	 * Whether the loop is running or not
	 *
	 * @var bool
	 */
	protected static bool $running = false;

	/**
	 * @param callable $callback
	 * @param int $interval in milliseconds
	 * @return void
	 */
	public static function run(callable $callback, int $interval = 500): void
	{
		static::$running = true;
		$last_hit = Toolkit::time();
		while (static::$running) {
			if (Toolkit::time() - $last_hit > $interval) {
				$callback();
				$last_hit = Toolkit::time();
			}
		}
	}

	/**
	 * @return void
	 */
	public static function stop(): void
	{
		static::$running = false;
	}

}