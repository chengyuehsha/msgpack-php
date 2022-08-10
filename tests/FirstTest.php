<?php

declare(strict_types=1);

use Chengyueh\MsgPack\Packer;
use PHPUnit\Framework\TestCase;

class FirstTest extends TestCase
{
    public function testExample(): void
    {
        $this->assertEquals('world', Packer::hello());
    }
}
