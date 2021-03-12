<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use stdClass;

class SkipClosureBindTo
{
    // see https://stackoverflow.com/q/39884308/1348344
    public function getPrivateProperty()
    {
        $randomGenerator = new stdClass();

        (function () {
            return $this->uuidV4Generator;
        })->bindTo($randomGenerator);
    }
}
