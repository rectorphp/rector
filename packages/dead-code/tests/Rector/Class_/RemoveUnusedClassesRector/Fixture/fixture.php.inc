<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

class NowhereUsedClass
{
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

?>
