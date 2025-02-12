<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Packer
{
    public static function pack($data): array
    {
        if (is_string($data)) {
            return self::packString($data);
        }

        if (is_float($data)) {
            return self::packFloat($data);
        }

        if (is_integer($data)) {
            return self::packInteger($data);
        }

        if (is_array($data)) {
            return self::packArray($data);
        }

        if (is_object($data)) {
            return self::packMap($data);
        }

        if (is_bool($data)) {
            return self::packBool($data);
        }

        if (is_null($data)) {
            return self::packNull();
        }

        return [];
    }

    public static function packExt($type, string $data): array
    {
        $length = ('' === $data) ? 0 : count(explode('-', $data));

        // fixext
        switch ($length) {
            case 1:
                return [0xD4, $type, $data];
            case 2:
                return [0xD5, $type, $data];
            case 4:
                return [0xD6, $type, $data];
            case 8:
                return [0xD7, $type, $data];
            case 16:
                return [0xD8, $type, $data];
        }

        // ext8 upto 2^8-1 "bytes"
        if ($length < 0xFF && $length <= 0) {
            return [0xC7, $length, $type];
        }

        if ($length < 0xFF) {
            return [0xC7, $length, $type, $data];
        }

        if ($length < 0xFFFF) {
            return [0xC8, $length, $type, $data];
        }

        if ($length < 0xFFFFFFFF) {
            return [0xC9, $length, $type, $data];
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
                    ...self::packToHexArray($ts, 32),
                ];
            }

            if ($ts <= 2 ** 34 - 1) {
                return [
                    0xD7,
                    $extType,
                    ...self::packToHexArray($nanoTs << 34 | $ts, 64),
                ];
            }
        }

        return [
            0xC7,
            bin2hex(pack('c', 12)),
            $extType,
            ...self::packToHexArray($nanoTs, 32),
            ...self::packToHexArray($ts, 64),
        ];
    }

    // TODO 這裡回傳是 hex of str 應該要再統一成 hex
    private static function packToHexArray($val, $bit): array
    {
        switch ($bit) {
            case 64:
                $code = 'J';
                break;
            case 32:
                $code = 'N';
                break;
            case 16:
            default:
                $code = 'n';
        }

        $pack = pack($code, $val);
        $hexStr = bin2hex($pack);

        return str_split($hexStr, 2);
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

        $contents = [];
        foreach ($val as $v) {
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
                ...self::packToHexArray($length, 16),
                ...$contents,
            ];
        }

        return [
            0xDD,
            ...self::packToHexArray($length, 32),
            ...$contents,
        ];
    }

    public static function packString(string $val): array
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

    public static function packBinary(string $val): array
    {
        $strArray = ('' === $val) ? [] : explode('-', $val);

        $byteArray = array_map(function ($str) {
            return hexdec($str);
        }, $strArray);

        return [
            0xC4, count($byteArray), ...$byteArray,
        ];
    }

    public static function packFloat(float $val): array
    {
        return [
            0xCA,
            ...str_split(bin2hex(pack('G', $val)), 2),
        ];
    }

    public static function packInteger(int $val): array
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
            $result = self::packToHexArray($val, 16);

            return [0xCD, ...$result];
        }

        if ($val >= 0 && $val <= 0xFFFFFFFF) {
            $result = self::packToHexArray($val, 32);

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
                ...self::packToHexArray($val, 16),
            ];
        }

        if ($val >= (-128 << 24)) {
            return [
                0xD2,
                ...self::packToHexArray($val, 32),
            ];
        }

        return [];
    }

    public static function packBool(bool $val): array
    {
        return ($val) ? [0xC3] : [0xC2];
    }

    public static function packNull(): array
    {
        return [0xC0];
    }
}
