<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

final class ExprBoolCaster
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private TypeUnwrapper $typeUnwrapper,
        private StaticTypeAnalyzer $staticTypeAnalyzer,
        private NodeFactory $nodeFactory
    ) {
    }

    public function boolCastOrNullCompareIfNeeded(Expr $expr): Expr
    {
        if (! $this->nodeTypeResolver->isNullableType($expr)) {
            if (! $this->isBoolCastNeeded($expr)) {
                return $expr;
            }

            return new Bool_($expr);
        }

        $exprStaticType = $this->nodeTypeResolver->getStaticType($expr);
        // if we remove null type, still has to be trueable
        if ($exprStaticType instanceof UnionType) {
            $unionTypeWithoutNullType = $this->typeUnwrapper->removeNullTypeFromUnionType($exprStaticType);
            if ($this->staticTypeAnalyzer->isAlwaysTruableType($unionTypeWithoutNullType)) {
                return new NotIdentical($expr, $this->nodeFactory->createNull());
            }
        } elseif ($this->staticTypeAnalyzer->isAlwaysTruableType($exprStaticType)) {
            return new NotIdentical($expr, $this->nodeFactory->createNull());
        }

        if (! $this->isBoolCastNeeded($expr)) {
            return $expr;
        }

        return new Bool_($expr);
    }

    private function isBoolCastNeeded(Expr $expr): bool
    {
        if ($expr instanceof BooleanNot) {
            return false;
        }

        if ($this->nodeTypeResolver->isStaticType($expr, BooleanType::class)) {
            return false;
        }

        return ! $expr instanceof BinaryOp;
    }
}
