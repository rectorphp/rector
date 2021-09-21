<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray4
{
    public function go($unknown)
    {
        [];
    }

    private function called()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray4
{
    public function go($unknown)
    {
        [];
    }
}

?>
