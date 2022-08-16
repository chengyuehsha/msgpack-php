<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Converter
{
    public static function byteArrayToHexArray(array $bytes, $separate = '-'): string
    {
        array_walk($bytes, function (&$byte) {
            $byte = is_int($byte) ? dechex($byte) : $byte;
            if (strlen($byte) <= 1) {
                $byte = '0' . $byte;
            }
        });

        return implode($separate, $bytes);
    }
}
