<?php

namespace Rector\Tests\Renaming\Rector\ClassMethod\RenameAnnotationRector\FixtureRenameEverywhere;

final class RenameEveryWhere
{
    /**
     * @psalm-ignore
     */
    public function test()
    {
        /** @psalm-ignore */
        $value = 1000;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\ClassMethod\RenameAnnotationRector\FixtureRenameEverywhere;

final class RenameEveryWhere
{
    /**
     * @phpstan-ignore
     */
    public function test()
    {
        /** @phpstan-ignore */
        $value = 1000;
    }
}

?>
