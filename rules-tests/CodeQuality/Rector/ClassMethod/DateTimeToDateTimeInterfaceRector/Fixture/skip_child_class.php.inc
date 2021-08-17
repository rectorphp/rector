<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime as PhpDateTime;

class DateTime extends PhpDateTime
{
    public function getDateTimeCustomFormat(): ?string
    {
        return $this->format('Y-m-d H:i:s');
    }
}

class Foo
{
    public static function bar(?DateTime $datetime)
    {
        // ...
    }

    public static function baz(DateTime $datetime)
    {
        // ...
    }
}
