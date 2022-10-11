<?php declare(strict_types=1);

namespace EasyHttp\Tests;


class AgentGeneratorTest extends \PHPUnit\Framework\TestCase
{

    public function testGenerate(): void
    {
        $this->assertIsString((new \EasyHttp\AgentGenerator)->generate());
    }

}
