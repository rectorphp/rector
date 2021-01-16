<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\FunctionLike;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

class SkipReprintUnionArray
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @return Yield_[]|YieldFrom[]
     */
    private function findCurrentScopeYieldNodes(FunctionLike $functionLike): array
    {
        $yieldNodes = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $functionLike->getStmts(),
            function (\PhpParser\Node $node) use (&$yieldNodes): ?int {
                if (! $node instanceof Yield_ && ! $node instanceof YieldFrom) {
                    return null;
                }

                $yieldNodes[] = $node;
                return null;
            }
        );

        return $yieldNodes;
    }
}
