<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

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

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return 5;
    }
}

?>
