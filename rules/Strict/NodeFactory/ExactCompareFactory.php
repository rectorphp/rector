<?php

declare(strict_types=1);

namespace Rector\Strict\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\NodeFactory;

final class ExactCompareFactory
{
    public function __construct(
        private NodeFactory $nodeFactory
    ) {
    }

    public function createIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNonEmpty): Expr|null
    {
        if ($exprType instanceof StringType) {
            return new Identical($expr, new String_(''));
        }

        if ($exprType instanceof IntegerType) {
            return new Identical($expr, new LNumber(0));
        }

        if ($exprType instanceof BooleanType) {
            return new Identical($expr, $this->nodeFactory->createFalse());
        }

        if ($exprType instanceof ArrayType) {
            return new Identical($expr, new Array_([]));
        }

        if (! $exprType instanceof UnionType) {
            return null;
        }

        return $this->createTruthyFromUnionType($exprType, $expr, $treatAsNonEmpty);
    }

    public function createNotIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNotEmpty): Expr|null
    {
        if ($exprType instanceof StringType) {
            return new NotIdentical($expr, new String_(''));
        }

        if ($exprType instanceof IntegerType) {
            return new NotIdentical($expr, new LNumber(0));
        }

        if ($exprType instanceof ArrayType) {
            return new NotIdentical($expr, new Array_([]));
        }

        if (! $exprType instanceof UnionType) {
            return null;
        }

        return $this->createFromUnionType($exprType, $expr, $treatAsNotEmpty);
    }

    private function createFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNotEmpty): Expr|null
    {
        $unionType = TypeCombinator::removeNull($unionType);

        if ($unionType instanceof BooleanType) {
            return new Identical($expr, $this->nodeFactory->createTrue());
        }

        if ($unionType instanceof TypeWithClassName) {
            return new Instanceof_($expr, new FullyQualified($unionType->getClassName()));
        }

        $nullConstFetch = $this->nodeFactory->createNull();
        $toNullNotIdentical = new NotIdentical($expr, $nullConstFetch);

        if ($unionType instanceof UnionType) {
            $compareExprs = [];

            foreach ($unionType->getTypes() as $unionedType) {
                $compareExprs[] = $this->createNotIdenticalFalsyCompare($unionedType, $expr, $treatAsNotEmpty);
            }

            /** @var Expr $truthyExpr */
            $truthyExpr = array_shift($compareExprs);
            foreach ($compareExprs as $compareExpr) {
                /** @var Expr $compareExpr */
                $truthyExpr = new BooleanOr($truthyExpr, $compareExpr);
            }

            return $truthyExpr;
        }

        $compareExpr = $this->createNotIdenticalFalsyCompare($unionType, $expr, $treatAsNotEmpty);
        if (! $compareExpr instanceof Expr) {
            return null;
        }

        return new BooleanAnd($toNullNotIdentical, $compareExpr);
    }

    private function createTruthyFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNonEmpty): Expr|null
    {
        $unionType = TypeCombinator::removeNull($unionType);

        if ($unionType instanceof UnionType) {
            $compareExprs = [];
            foreach ($unionType->getTypes() as $unionedType) {
                $compareExprs[] = $this->createIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty);
            }

            /** @var Expr $truthyExpr */
            $truthyExpr = array_shift($compareExprs);
            foreach ($compareExprs as $compareExpr) {
                /** @var Expr $compareExpr */
                $truthyExpr = new BooleanOr($truthyExpr, $compareExpr);
            }

            return $truthyExpr;
        }

        if ($unionType instanceof BooleanType) {
            return new Identical($expr, $this->nodeFactory->createTrue());
        }

        if ($unionType instanceof TypeWithClassName) {
            $instanceOf = new Instanceof_($expr, new FullyQualified($unionType->getClassName()));
            return new BooleanNot($instanceOf);
        }

        $toNullIdentical = new Identical($expr, $this->nodeFactory->createNull());

        // assume we have to check empty string, integer and bools
        if (! $treatAsNonEmpty) {
            $scalarFalsyIdentical = $this->createIdenticalFalsyCompare($unionType, $expr, $treatAsNonEmpty);
            if (! $scalarFalsyIdentical instanceof Expr) {
                return null;
            }

            return new BooleanOr($toNullIdentical, $scalarFalsyIdentical);
        }

        return $toNullIdentical;
    }
}
