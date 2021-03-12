<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use stdClass;

class SkipClosureProperty
{
    // see https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
    public function getPrivateProperty()
    {
        $randomGenerator = new stdClass();

        $setUuidV4Generator = \Closure::bind(function () {
            return $this->uuidV4Generator;
        }, $randomGenerator, $randomGenerator)();
    }
}
