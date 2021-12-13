<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class CastArrayKeyLastValue
{
    public function run(ClassMethod $classMethod)
    {
        $lastItemKey = array_key_last((array) $classMethod->stmts);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class CastArrayKeyLastValue
{
    public function run(ClassMethod $classMethod)
    {
        $stmts = (array) $classMethod->stmts;
        end($stmts);
        $lastItemKey = key($stmts);
    }
}

?>
