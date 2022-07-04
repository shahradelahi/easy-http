<?php

namespace EasyHttp\Test;

use EasyHttp\Loop;
use EasyHttp\Utils\Toolkit;

class LoopTest extends \PHPUnit\Framework\TestCase
{

	public function test_count_down(): void
	{
		$runs = [];
		$end_time = Toolkit::time() + 1000;

		Loop::run(function () use (&$runs, $end_time) {
			$runs[] = Toolkit::time();

			if (Toolkit::time() > $end_time) {
				Loop::stop();
			}
		}, 100);

		$this->assertCount(10, $runs);
	}

}