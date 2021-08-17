<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime;
use DateTimeImmutable;

class SkipUnionType
{
    public function run(DateTime|DateTimeImmutable $dateTime)
    {
    }
}
