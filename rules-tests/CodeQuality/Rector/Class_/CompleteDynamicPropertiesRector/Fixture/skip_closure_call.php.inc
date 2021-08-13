<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use stdClass;

class SkipClosureCall
{
    public function getPrivateProperty()
    {
        $randomGenerator = new stdClass();

        (function () {
            return $this->uuidV4Generator;
        })->call($randomGenerator);
    }
}
