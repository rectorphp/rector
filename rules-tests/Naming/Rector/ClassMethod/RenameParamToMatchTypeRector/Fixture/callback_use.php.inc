<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class CallbackUse
{
    public function run(ClassMethod $node, array $items)
    {
        array_walk($node->stmts, function (Node $stmt) use ($node) {
            return $stmt + $node;
        });

        function someFunction($node)
        {
        }
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class CallbackUse
{
    public function run(ClassMethod $classMethod, array $items)
    {
        array_walk($classMethod->stmts, function (Node $stmt) use ($classMethod) {
            return $stmt + $classMethod;
        });

        function someFunction($node)
        {
        }
    }
}

?>
