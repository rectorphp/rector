<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run()
    {
    }
}

?>
