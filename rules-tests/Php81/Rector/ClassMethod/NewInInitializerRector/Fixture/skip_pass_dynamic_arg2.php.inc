<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;

class SkipPassDynamicArg2
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $timezone = 'Asia/Jakarta';
        $this->dateTime = $dateTime ?? new DateTime('now', new DateTimeZone($timezone));
    }
}
