<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;

class SkipPassDynamicArg
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $variable = 'now';
        $this->dateTime = $dateTime ?? new DateTime($variable);
    }
}
