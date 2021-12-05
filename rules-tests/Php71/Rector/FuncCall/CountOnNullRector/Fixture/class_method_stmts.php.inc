<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

final class ClassMethodStmts
{
    public function run(ClassMethod $classMethod)
    {
        return count($classMethod->stmts) === 5;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

final class ClassMethodStmts
{
    public function run(ClassMethod $classMethod)
    {
        return count((array) $classMethod->stmts) === 5;
    }
}

?>
