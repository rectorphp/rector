<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray2
{
    public function go($unknown)
    {
        ['a', 'b'];
    }

    private function called()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray2
{
    public function go($unknown)
    {
        ['a', 'b'];
    }
}

?>
