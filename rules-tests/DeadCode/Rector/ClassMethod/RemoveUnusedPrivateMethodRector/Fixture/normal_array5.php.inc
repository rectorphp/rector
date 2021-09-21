<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray5
{
    public function go($a, $b)
    {
        return [$a, $b];
    }

    private function called()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray5
{
    public function go($a, $b)
    {
        return [$a, $b];
    }
}

?>
