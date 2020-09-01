<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;

class SkipReprintUnionArray
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }
    /**
     * @return Yield_[]|YieldFrom[]
     */
    private function findCurrentScopeYieldNodes(FunctionLike $functionLike): array
    {
        $yieldNodes = [];

        $this->callableNodeTraverser->traverseNodesWithCallable(
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
