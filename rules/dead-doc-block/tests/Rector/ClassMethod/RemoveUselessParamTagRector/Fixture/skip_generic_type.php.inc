<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

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
