<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\Fixture;

use stdClass;

class SkipPublicSomePropertyReadonly
{
    public function __construct(public readonly stdClass $someUnusedDependency)
    {
    }
}
