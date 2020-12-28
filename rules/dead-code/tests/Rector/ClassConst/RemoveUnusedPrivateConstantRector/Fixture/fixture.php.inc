<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class Fixture
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return 5;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return 5;
    }
}

?>
