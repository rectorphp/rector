<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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
