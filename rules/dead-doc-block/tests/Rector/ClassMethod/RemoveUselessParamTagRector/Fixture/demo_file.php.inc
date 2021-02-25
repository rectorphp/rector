<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

final class DemoFile
{
    /**
     * @param iterable<stdClass> $foo
     */
    public function run(iterable $foo)
    {
        yield from $foo;
    }
}
?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector\Fixture;

final class DemoFile
{
    /**
     * @param iterable<stdClass> $foo
     */
    public function run(iterable $foo)
    {
    }
}
?>
