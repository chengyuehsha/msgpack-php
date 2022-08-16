<?php

declare(strict_types=1);

use Chengyueh\MsgPack\Converter;
use Chengyueh\MsgPack\Packer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FormatTest extends TestCase
{
    private $testSuitPath = __DIR__ . '/../msgpack-test-suite/src';

    public function testOffcialExample(): void
    {
        $input = json_decode('{"compact":true,"schema":0}', false);
        $expect = '82 a7 63 6f 6d 70 61 63 74 c3 a6 73 63 68 65 6d 61 00';

        $result = Packer::pack($input);
        $result = Converter::byteArrayToHexArray($result, ' ');
        $this->assertEquals($expect, $result);
    }

    public function testExt(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/60.ext.yaml');
        foreach ($cases as $case) {
            $input = $case['ext'];
            $expect = $case['msgpack'][0];

            $result = Packer::packExt($input[0], $input[1]);
            $result = Converter::byteArrayToHexArray($result);
            $this->assertEquals($expect, $result, json_encode($input));
        }
    }

    public function testTimestamp(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/50.timestamp.yaml');
        foreach ($cases as $case) {
            $input = $case['timestamp'];
            $expect = $case['msgpack'][0];

            $result = Packer::packTimestamp($input);
            $result = Converter::byteArrayToHexArray($result);
            $this->assertEquals($expect, $result, json_encode($input));
        }
    }

    public function testBinary(): void
    {
        $cases = Yaml::parseFile($this->testSuitPath . '/12.binary.yaml');
        foreach ($cases as $case) {
            $input = $case['binary'];
            $expect = $case['msgpack'][0];

            $result = Packer::packBinary($input);
            $result = Converter::byteArrayToHexArray($result);
            $this->assertEquals($expect, $result);
        }
    }

    /**
     * @dataProvider providePack
     */
    public function testPack($type, $input, $expect): void
    {
        $result = Packer::pack($input);
        $result = Converter::byteArrayToHexArray($result);
        $this->assertEquals($expect, $result);
    }

    public function providePack(): Generator
    {
        $testSuitsMap = [
            'nil' => [
                '/10.nil.yaml',
            ],
            'bool' => [
                '/11.bool.yaml',
            ],
            'number' => [
                '/20.number-positive.yaml',
                '/21.number-negative.yaml',
                '/22.number-float.yaml',
            ],
            'string' => [
                '/30.string-ascii.yaml',
                '/31.string-utf8.yaml',
                '/32.string-emoji.yaml',
            ],
            'array' => [
                '/40.array.yaml',
            ],
            'map' => [
                '/41.map.yaml',
            ],
            'nested' => [
                '/42.nested.yaml',
            ],
        ];

        foreach ($testSuitsMap as $type => $files) {
            foreach ($files as $fileName) {
                // 加上 PARSE_OBJECT_FOR_MAP 為了要正確判斷 array 或 map
                $file = $this->testSuitPath . $fileName;
                $cases = Yaml::parseFile($file, Yaml::PARSE_OBJECT_FOR_MAP);

                foreach ($cases as $case) {
                    $expect = $case->msgpack[0];

                    // workaround, 因為 nested type 裡面會有 map or array 兩種
                    $input = $case->$type ?? $case->map ?? $case->array;

                    yield [$type, $input, $expect];
                }
            }
        }
    }
}
