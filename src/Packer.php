<?php

declare(strict_types=1);

namespace Chengyueh\MsgPack;

class Packer
{
    public static function nil($val): int
    {
        return 0xC0;
    }
}
