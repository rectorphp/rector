<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use stdClass;

class SkipDifferentType
{
    public static function run(stdClass $stdClass)
    {
    }
}
