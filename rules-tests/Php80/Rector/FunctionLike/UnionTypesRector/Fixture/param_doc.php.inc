<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class ParamDoc
{
    /**
     * @param ClassMethod|Property|ClassConst|ClassMethod $stmt
     */
    public function addAsFirstMethod(Class_ $class, Stmt $stmt): void
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class ParamDoc
{
    public function addAsFirstMethod(Class_ $class, \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassConst $stmt): void
    {
    }
}

?>
