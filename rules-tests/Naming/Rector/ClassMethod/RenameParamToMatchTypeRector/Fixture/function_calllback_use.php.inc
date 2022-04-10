<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

function FunctionCallbackUse(ClassMethod $node, array $items)
{
    array_walk($node->stmts, function (Node $stmt) use ($node) {
        return $stmt + $node;
    });

    function someFunction($node)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

function FunctionCallbackUse(ClassMethod $classMethod, array $items)
{
    array_walk($classMethod->stmts, function (Node $node) use ($classMethod) {
        return $node + $classMethod;
    });

    function someFunction($node)
    {
    }
}

?>
