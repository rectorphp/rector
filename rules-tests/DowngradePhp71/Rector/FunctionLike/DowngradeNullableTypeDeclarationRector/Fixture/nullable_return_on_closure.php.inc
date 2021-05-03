<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

class NullableOnClosure
{
    public function run()
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, &$assignedParamName) : ?int {
            return null;
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector\Fixture;

class NullableOnClosure
{
    public function run()
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, &$assignedParamName) {
            return null;
        });
    }
}

?>
