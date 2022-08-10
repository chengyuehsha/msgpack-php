<?php

declare(strict_types=1);

use Chengyueh\MsgPack\Packer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FormatTest extends TestCase
{
    public function testNull(): void
    {
        $case = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/10.nil.yaml');
        $input = $case[0]['nil'];
        $expect = $case[0]['msgpack'][0];

        $this->assertEquals($expect, dechex(Packer::nil($case[0]['nil'])));
    }
}
