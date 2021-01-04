<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanNot\SimplifyDeMorganBinaryRector\Fixture;

class SkipBinaryAnd
{
    public static function chr_to_decimal(string $char): int
    {
        $code = ord($char[0]);

        if (!($code & 0x80)) {
            return $code;
        }

        return $code;
    }
}
