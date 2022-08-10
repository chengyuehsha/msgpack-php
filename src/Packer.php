<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Packer
{
    public static function bool(bool $val): int
    {
        return ($val) ? 0xC3 : 0xC2;
    }

    public static function nil($val): int
    {
        return 0xC0;
    }
}
