<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class CastArrayKeyFirstValue
{
    public function run(ClassMethod $classMethod)
    {
        $firstItemKey = array_key_first((array) $classMethod->stmts);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

class CastArrayKeyFirstValue
{
    public function run(ClassMethod $classMethod)
    {
        $stmts = (array) $classMethod->stmts;
        reset($stmts);
        $firstItemKey = key($stmts);
    }
}

?>
