<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

final class SkipGenericType
{
    /**
     * @param iterable<stdClass> $foo
     */
    public function run(iterable $foo)
    {
    }
}
?>
