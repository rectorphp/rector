<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ParamAnalyzer
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }

    public function isParamUsedInClassMethod(ClassMethod $classMethod, Param $param): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $param
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node, $param->var);
        });
    }

    /**
     * @param Param[] $params
     */
    public function hasPropertyPromotion(array $params): bool
    {
        foreach ($params as $param) {
            if ($param->flags !== 0) {
                return true;
            }
        }

        return false;
    }
}
