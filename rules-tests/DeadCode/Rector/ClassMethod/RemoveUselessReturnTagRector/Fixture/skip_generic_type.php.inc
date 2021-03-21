<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use stdClass;

final class SkipGenericType
{
    /**
     * @return iterable<stdClass>
     */
    public function run(): iterable
    {
        yield new stdClass;
    }
}
