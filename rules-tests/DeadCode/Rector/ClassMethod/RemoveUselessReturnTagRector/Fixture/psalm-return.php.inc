<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class PsalmReturn
{
    /**
     * @return string
     * @psalm-return 'blah-string'
     */
    public function blah(): string
    {
        return 'blah-string';
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class PsalmReturn
{
    /**
     * @psalm-return 'blah-string'
     */
    public function blah(): string
    {
        return 'blah-string';
    }
}
?>
