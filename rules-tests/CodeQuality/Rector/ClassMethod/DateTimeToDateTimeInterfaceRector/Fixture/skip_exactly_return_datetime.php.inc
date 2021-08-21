<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime;

class SkipExactlyReturnDateTime
{
    public static function run(): DateTime
    {
        return new DateTime('now');
    }

    public static function run2(): DateTime
    {
        $obj = new DateTime('now');
        return $obj;
    }

    public static function run3(): DateTime
    {
        $obj = new DateTime('now');
        $obj2 = clone $obj;

        return $obj2;
    }

    public static function run4(): DateTime
    {
        $obj = new DateTime('now');
        return clone $obj;
    }
}
