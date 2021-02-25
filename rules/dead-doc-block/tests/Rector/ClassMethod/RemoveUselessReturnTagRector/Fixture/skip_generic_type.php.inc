<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

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
