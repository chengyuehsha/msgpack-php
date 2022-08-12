<?php

declare(strict_types=1);

use Chengyueh\MsgPack\Packer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FormatTest extends TestCase
{
    public function testFloat(): void
    {
        $cases = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/22.number-float.yaml');
        foreach ($cases as $case) {
            $input = $case['number'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::float($input));
            $this->assertEquals($expect, $result);
            var_dump($result);
        }
    }

    public function testInt(): void
    {
        $cases = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/20.number-positive.yaml');
        foreach ($cases as $case) {
            $input = $case['number'];
            $expect = $case['msgpack'][0];

            $result = $this->convertByteArrayToHexString(Packer::int($input));
            $this->assertEquals($expect, $result);
        }

        $cases = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/21.number-negative.yaml');
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

    public function testBool(): void
    {
        $cases = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/11.bool.yaml');
        foreach ($cases as $case) {
            $input = $case['bool'];
            $expect = $case['msgpack'][0];

            $this->assertEquals($expect, dechex(Packer::bool($input)));
        }
    }

    public function testNull(): void
    {
        $case = Yaml::parseFile(__DIR__ . '/../msgpack-test-suite/src/10.nil.yaml');
        $input = $case[0]['nil'];
        $expect = $case[0]['msgpack'][0];

        $this->assertEquals($expect, dechex(Packer::nil($case[0]['nil'])));
    }
}
