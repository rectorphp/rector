<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray3
{
    public function go($unknown)
    {
        ['a', $unknown];
    }

    private function called()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray3
{
    public function go($unknown)
    {
        ['a', $unknown];
    }
}

?>
