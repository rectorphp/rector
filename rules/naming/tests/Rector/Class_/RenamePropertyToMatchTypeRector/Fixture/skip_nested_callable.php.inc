<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class SkipNestedCallable
{
    public function run(ClassMethod $node, array $items)
    {
        array_walk($node->stmts, function (Node $node) {
            return $node;
        });

        function someFunction($node)
        {
        }
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class SkipNestedCallable
{
    public function run(ClassMethod $classMethod, array $items)
    {
        array_walk($classMethod->stmts, function (Node $node) {
            return $node;
        });

        function someFunction($node)
        {
        }
    }
}

?>
