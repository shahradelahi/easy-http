<?php declare(strict_types=1);

namespace EasyHttp\Tests;

use EasyHttp\Loop;
use EasyHttp\Utils\Toolkit;

class LoopTest extends \PHPUnit\Framework\TestCase
{

    public function testCountDown(): void
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