<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class DoubleCast
{
    public function run(ClassMethod $classMethod)
    {
        $lastItemKey = array_key_last((array)(array) $classMethod->stmts);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class DoubleCast
{
    public function run(ClassMethod $classMethod)
    {
        $stmts = (array)(array) $classMethod->stmts;
        end($stmts);
        $lastItemKey = key($stmts);
    }
}

?>
