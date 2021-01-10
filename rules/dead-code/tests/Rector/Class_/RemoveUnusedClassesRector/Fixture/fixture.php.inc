<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

class NowhereUsedClass
{
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Source\SomeInterface;

?>
