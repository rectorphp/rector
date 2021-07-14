<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class ParamAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator,
        private ValueResolver $valueResolver
    ) {
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

    public function isNullable(Param $param): bool
    {
        if ($param->variadic) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        return $param->type instanceof NullableType;
    }

    public function hasDefaultNull(Param $param): bool
    {
        return $param->default instanceof ConstFetch && $this->valueResolver->isNull($param->default);
    }
}
