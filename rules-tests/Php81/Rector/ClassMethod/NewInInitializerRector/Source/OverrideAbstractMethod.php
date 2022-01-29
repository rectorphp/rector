<?php

declare(strict_types=1);

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source;

use DateTime;

abstract class OverrideAbstractMethod
{
    abstract public function __construct(
        ?DateTime $dateTime = null
    );
}
