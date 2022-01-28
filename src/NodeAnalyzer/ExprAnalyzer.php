<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class ExprAnalyzer
{
    public function __construct(
        private readonly NodeComparator $nodeComparator,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isNonTypedFromParam(Expr $expr): bool
    {
        if (! $expr instanceof Variable) {
            return false;
        }

        $functionLike = $this->betterNodeFinder->findParentType($expr, FunctionLike::class);
        if (! $functionLike instanceof FunctionLike) {
            return false;
        }

        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (! $this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }

            return $param->type === null;
        }

        return false;
    }

    public function isDynamicArray(Array_ $array): bool
    {
        foreach ($array->items as $item) {
            if (! $item instanceof ArrayItem) {
                continue;
            }

            $key = $item->key;

            if (! $this->isAllowedArrayKey($key)) {
                return true;
            }

            $value = $item->value;
            if (! $this->isAllowedArrayValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function isAllowedArrayKey(?Expr $expr): bool
    {
        if (! $expr instanceof Expr) {
            return true;
        }

        return in_array($expr::class, [String_::class, LNumber::class], true);
    }

    private function isAllowedArrayValue(Expr $expr): bool
    {
        if ($expr instanceof Array_) {
            return true;
        }

        return $this->isAllowedArrayOrScalar($expr);
    }

    private function isAllowedArrayOrScalar(Expr $expr): bool
    {
        if (! $expr instanceof Array_) {
            return $expr instanceof Scalar;
        }

        return ! $this->isDynamicArray($expr);
    }
}
