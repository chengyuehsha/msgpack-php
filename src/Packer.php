<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Packer
{
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
