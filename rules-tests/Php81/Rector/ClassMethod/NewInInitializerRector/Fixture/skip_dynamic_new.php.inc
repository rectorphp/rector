<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use stdClass;

class SkipDynamicNew
{
    private object $stdClass;

    public function __construct(
        string $fallbackClass,
        ?stdClass $stdClass = null
    ) {
        $this->stdClass = $stdClass ?? new $fallbackClass;
    }
}
