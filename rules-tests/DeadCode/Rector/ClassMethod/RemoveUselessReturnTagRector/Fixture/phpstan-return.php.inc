<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

final class PhpstanReturn
{
    /**
     * @return string
     * @phpstan-return 'blah-string'
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

final class PhpstanReturn
{
    /**
     * @phpstan-return 'blah-string'
     */
    public function blah(): string
    {
        return 'blah-string';
    }
}
?>
