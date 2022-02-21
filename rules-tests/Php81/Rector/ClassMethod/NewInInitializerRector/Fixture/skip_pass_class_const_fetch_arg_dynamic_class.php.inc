<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\SomeValueObject;

class SkipPassClassConstFetchArgDynamicClass
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $class = SomeValueObject::class;
        $this->dateTime = $dateTime ?? new DateTime($class::NOW);
    }
}
