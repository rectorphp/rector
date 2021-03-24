<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class DemoFile
{
    /**
     * @return static
     */
    public function run(): self
    {
        return new self();
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class DemoFile
{
    /**
     * @return static
     */
    public function run(): self
    {
        return new self();
    }
}
?>
