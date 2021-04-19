<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class RemovePhpDoc
{
    /**
     * @param int $notUsed interesting comment
     */
    private function run(int $notUsed)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class RemovePhpDoc
{
    private function run()
    {
    }
}

?>
