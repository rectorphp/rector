<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray
{
    public function go($unknown)
    {
        call_user_func(['a', 'b']);
    }

    private function called()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class NormalArray
{
    public function go($unknown)
    {
        call_user_func(['a', 'b']);
    }
}

?>
