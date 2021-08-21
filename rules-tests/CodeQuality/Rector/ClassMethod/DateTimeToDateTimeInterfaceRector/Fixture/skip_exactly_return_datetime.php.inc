<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime;

class SkipExactlyReturnDateTime
{
    public static function run(): DateTime
    {
        return new DateTime('now');
    }
}
