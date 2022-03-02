<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class ExprAnalyzer
{
    public function __construct(
        private readonly NodeComparator $nodeComparator,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ArrayManipulator $arrayManipulator
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

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);

        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (! $this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }

            $paramName = $this->nodeNameResolver->getName($param->var);

            if ($paramName === null) {
                continue;
            }

            $paramTag = $phpDocInfo->getParamTagValueByName($paramName);

            return $paramTag instanceof ParamTagValueNode && $param->type === null;
        }

        return false;
    }

    public function isDynamicValue(Expr $expr): bool
    {
        if (! $expr instanceof Array_) {
            if ($expr instanceof Scalar) {
                return false;
            }

            return ! $this->isAllowedConstFetchOrClassConstFeth($expr);
        }

        return $this->arrayManipulator->isDynamicArray($expr);
    }

    private function isAllowedConstFetchOrClassConstFeth(Expr $expr): bool
    {
        if (! in_array($expr::class, [ConstFetch::class, ClassConstFetch::class], true)) {
            return false;
        }

        if ($expr instanceof ClassConstFetch) {
            return $expr->class instanceof Name && $expr->name instanceof Identifier;
        }

        return true;
    }
}
