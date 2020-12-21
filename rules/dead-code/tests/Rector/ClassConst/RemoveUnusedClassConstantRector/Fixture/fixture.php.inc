<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class Fixture
{
    public function run()
    {
    }
}

?>
