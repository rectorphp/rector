<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class RemoveLast
{
    private function run(string $used, int $notUsed)
    {
        echo $used;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class RemoveLast
{
    private function run(string $used)
    {
        echo $used;
    }
}

?>
