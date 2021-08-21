<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime as PhpDateTime;

class ReturnDateTime extends PhpDateTime
{
    public function getDateTimeCustomFormat(): ?string
    {
        return $this->format('Y-m-d H:i:s');
    }
}

class SkipChildClassReturn
{
    public static function bar(): ?ReturnDateTime
    {
        // ...
    }

    public static function baz(): ReturnDateTime
    {
        // ...
    }
}
