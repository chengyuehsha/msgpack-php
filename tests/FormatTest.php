<?php

declare(strict_types=1);

use Chengyueh\MsgPack\Packer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FormatTest extends TestCase
{
    private $testSuitPath = __DIR__ . '/../msgpack-test-suite/src';

    public function testArray(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/40.array.yaml');
        foreach ($cases as $case) {
            $input = $case['array'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::packArray($input));
            $this->assertEquals($expect, $result);
        }
    }

    public function testString(): void
    {
        $cases = [
            ...Yaml::parseFile($this->testSuitPath . '/30.string-ascii.yaml'),
            ...Yaml::parseFile($this->testSuitPath . '/31.string-utf8.yaml'),
            ...Yaml::parseFile($this->testSuitPath . '/32.string-emoji.yaml'),
        ];

        foreach ($cases as $case) {
            $input = $case['string'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::str($input));
            $this->assertEquals($expect, $result);
        }
    }

    public function testFloat(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/22.number-float.yaml');
        foreach ($cases as $case) {
            $input = $case['number'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::float($input));
            $this->assertEquals($expect, $result);
        }
    }

    public function testInt(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/20.number-positive.yaml');
        foreach ($cases as $case) {
            $input = $case['number'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::int($input));
            $this->assertEquals($expect, $result);
        }

        $cases = Yaml::parseFile($this->testSuitPath . '/21.number-negative.yaml');
        foreach ($cases as $case) {
            $input = $case['number'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::int($input));
            $this->assertEquals($expect, $result);
        }
    }

    private function convertByteArrayToHexString(array $bytes, $separate = '-'): string
    {
        array_walk($bytes, function (&$byte) {
            $byte = is_int($byte) ? dechex($byte) : $byte;
            if (strlen($byte) <= 1) {
                $byte = '0' . $byte;
            }
        });

        return implode($separate, $bytes);
    }

    public function testBinary(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/12.binary.yaml');
        foreach ($cases as $case) {
            $input = $case['binary'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::binary($input));
            $this->assertEquals($expect, $result);
        }
    }

    public function testBool(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/11.bool.yaml');
        foreach ($cases as $case) {
            $input = $case['bool'];
            $expect = $case['msgpack'][0];

            $this->assertEquals($expect, dechex(Packer::bool($input)));
        }
    }

    public function testNull(): void
    {
        $case = Yaml::parseFile($this->testSuitPath . '/10.nil.yaml');
        $input = $case[0]['nil'];
        $expect = $case[0]['msgpack'][0];

        $this->assertEquals($expect, dechex(Packer::nil($case[0]['nil'])));
    }
}
