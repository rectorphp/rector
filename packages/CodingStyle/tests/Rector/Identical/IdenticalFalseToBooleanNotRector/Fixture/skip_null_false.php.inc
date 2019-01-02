<?php

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

class SkipNullFalse
{
    public function run($str)
    {
        if (\strpos($str, "\xC2") === false) {
            return $str;
        }
    }
}
