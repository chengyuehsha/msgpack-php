<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Packer
{
    public static function pack($data): array
    {
        if (is_string($data)) {
            return self::str($data);
        }

        if (is_integer($data)) {
            return self::int($data);
        }

        if (is_array($data)) {
            return self::packArray($data);
        }

        if (is_object($data)) {
            return self::packMap($data);
        }

        return [];
    }

    public static function packTimestamp($data): array
    {
        $ts = $data[0];
        $nanoTs = $data[1];

        // Timestamp is assigned to type -1
        $extType = bin2hex(pack('c', -1));

        if ($ts >= 0) {
            if ($nanoTs <= 0 && $ts <= 2 ** 32 - 1) {
                return [
                    0xD6,
                    $extType,
                    ...str_split(bin2hex(pack('N', $ts)), 2),
                ];
            }

            if ($ts <= 2 ** 34 - 1) {
                return [
                    0xD7,
                    $extType,
                    ...str_split(bin2hex(pack('J', $nanoTs << 34 | $ts)), 2),
                ];
            }
        }

        return [
            0xC7,
            bin2hex(pack('c', 12)),
            $extType,
            ...str_split(bin2hex(pack('N', $nanoTs)), 2),
            ...str_split(bin2hex(pack('J', $ts)), 2),
        ];
    }

    public static function packMap(\stdClass $data): array
    {
        $length = count((array) $data);
        $result = [0x80 | $length];

        foreach ($data as $key => $value) {
            array_push($result, ...self::pack($key));
            array_push($result, ...self::pack($value));
        }

        return $result;
    }

    public static function packArray(array $val): array
    {
        $length = count($val);

        // 用 recursive 處理？不然沒完沒了..
        $contents = [];
        foreach ($val as $v) {
            if (is_integer($v)) {
                $contents[] = $v;
                continue;
            }

            array_push($contents, ...self::pack($v));
        }

        // 4-bit
        if ($length <= 0xF) {
            return [
                0x90 | $length,
                ...$contents,
            ];
        }

        if ($length <= 0xFFFF) {
            return [
                0xDC,
                ...str_split(bin2hex(pack('n', $length)), 2),
                ...$contents,
            ];
        }

        return [
            0xDD,
            ...str_split(bin2hex(pack('N', $length)), 2),
            ...$contents,
        ];
    }

    public static function str(string $val): array
    {
        $length = strlen($val);

        // 5-bit
        if ($length <= 0x1F) {
            return [
                0xA0 | $length,
                ...self::strToByteArray($val),
            ];
        }

        if ($length <= 0xFF) {
            return [
                dechex(0xD9),
                dechex($length),
                ...self::strToByteArray($val),
            ];
        }

        if ($length <= 0xFFFF) {
            return [
                dechex(0xDA),
                dechex($length),
                ...self::strToByteArray($val),
            ];
        }

        if ($length <= 0xFFFFFFFF) {
            return [
                dechex(0xDB),
                dechex($length),
                ...self::strToByteArray($val),
            ];
        }
    }

    private static function strToByteArray(string $str): array
    {
        if (0 === strlen($str)) {
            return [];
        }

        foreach (str_split($str) as $chr) {
            $result[] = ord($chr);
        }

        return $result;
    }

    public static function binary(string $val): array
    {
        $strArray = ('' === $val) ? [] : explode('-', $val);

        $byteArray = array_map(function ($str) {
            return hexdec($str);
        }, $strArray);

        return [
            0xC4, count($byteArray), ...$byteArray,
        ];
    }

    public static function float(float $val): array
    {
        return [
            0xCA,
            ...str_split(bin2hex(pack('G', $val)), 2),
        ];
    }

    public static function int(int $val): array
    {
        // 7-bit positive integer
        if ($val >= 0 && $val <= 127) {
            return [$val];
        }

        // 8-bit unsigned
        if ($val >= 0 && $val <= 0xFF) {
            return [0xCC, $val];
        }

        if ($val >= 0 && $val <= 0xFFFF) {
            // TODO 這裡回傳是 str 應該要統一成 int
            $result = str_split(bin2hex(pack('n', $val)), 2);

            return [0xCD, ...$result];
        }

        if ($val >= 0 && $val <= 0xFFFFFFFF) {
            $result = str_split(bin2hex(pack('N', $val)), 2);

            return [0xCE, ...$result];
        }

        // 5-bit negative integer
        if ($val >= -32) {
            return [
                bin2hex(pack('c', 0xE0 | $val)),
            ];
        }

        // 8-bit signed
        if ($val >= -128) {
            return [
                0xD0,
                bin2hex(pack('c', $val)),
            ];
        }

        if ($val >= (-128 << 8)) {
            return [
                0xD1,
                ...str_split(bin2hex(pack('n', $val)), 2),
            ];
        }

        if ($val >= (-128 << 24)) {
            return [
                0xD2,
                ...str_split(bin2hex(pack('N', $val)), 2),
            ];
        }

        return [];
    }

    public static function bool(bool $val): int
    {
        return ($val) ? 0xC3 : 0xC2;
    }

    public static function nil($val): int
    {
        return 0xC0;
    }
}
