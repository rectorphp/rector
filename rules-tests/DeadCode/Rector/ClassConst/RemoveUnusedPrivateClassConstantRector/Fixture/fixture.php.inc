<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

class Fixture
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

class Fixture
{
    public function run()
    {
    }
}

?>
