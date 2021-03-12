<?php

namespace Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\FixturePostImport;

final class InstanceofSomeClass
{
    public function run($value)
    {
        if ($value instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\GetClassOnNullRector\FixturePostImport;

use PhpParser\Node\Stmt\ClassMethod;
final class InstanceofSomeClass
{
    public function run($value)
    {
        if ($value instanceof ClassMethod) {
            return true;
        }

        return false;
    }
}

?>
