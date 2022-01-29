<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\OverrideAbstractMethod;

class SkipOverrideAbstractMethod extends OverrideAbstractMethod
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $this->dateTime = $dateTime ?? new DateTime('now');
    }
}
