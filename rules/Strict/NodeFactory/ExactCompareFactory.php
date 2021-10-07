<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function createIdenticalFalsyCompare(\PHPStan\Type\Type $exprType, \PhpParser\Node\Expr $expr, bool $treatAsNonEmpty)
    {
        if ($exprType instanceof \PHPStan\Type\StringType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Scalar\String_(''));
        }
        if ($exprType instanceof \PHPStan\Type\IntegerType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Scalar\LNumber(0));
        }
        if ($exprType instanceof \PHPStan\Type\BooleanType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createFalse());
        }
        if ($exprType instanceof \PHPStan\Type\ArrayType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Expr\Array_([]));
        }
        if (!$exprType instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        return $this->createTruthyFromUnionType($exprType, $expr, $treatAsNonEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function createNotIdenticalFalsyCompare(\PHPStan\Type\Type $exprType, \PhpParser\Node\Expr $expr, bool $treatAsNotEmpty)
    {
        if ($exprType instanceof \PHPStan\Type\StringType) {
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, new \PhpParser\Node\Scalar\String_(''));
        }
        if ($exprType instanceof \PHPStan\Type\IntegerType) {
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, new \PhpParser\Node\Scalar\LNumber(0));
        }
        if ($exprType instanceof \PHPStan\Type\ArrayType) {
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, new \PhpParser\Node\Expr\Array_([]));
        }
        if (!$exprType instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        return $this->createFromUnionType($exprType, $expr, $treatAsNotEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function createFromUnionType(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr, bool $treatAsNotEmpty)
    {
        $unionType = \PHPStan\Type\TypeCombinator::removeNull($unionType);
        if ($unionType instanceof \PHPStan\Type\BooleanType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createTrue());
        }
        if ($unionType instanceof \PHPStan\Type\TypeWithClassName) {
            return new \PhpParser\Node\Expr\Instanceof_($expr, new \PhpParser\Node\Name\FullyQualified($unionType->getClassName()));
        }
        $nullConstFetch = $this->nodeFactory->createNull();
        $toNullNotIdentical = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, $nullConstFetch);
        if ($unionType instanceof \PHPStan\Type\UnionType) {
            return $this->resolveFromCleanedNullUnionType($unionType, $expr, $treatAsNotEmpty);
        }
        $compareExpr = $this->createNotIdenticalFalsyCompare($unionType, $expr, $treatAsNotEmpty);
        if (!$compareExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($toNullNotIdentical, $compareExpr);
    }
    private function resolveFromCleanedNullUnionType(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr, bool $treatAsNotEmpty) : ?\PhpParser\Node\Expr
    {
        $compareExprs = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $compareExprs[] = $this->createNotIdenticalFalsyCompare($unionedType, $expr, $treatAsNotEmpty);
        }
        /** @var Expr $truthyExpr */
        $truthyExpr = \array_shift($compareExprs);
        foreach ($compareExprs as $compareExpr) {
            if (!$compareExpr instanceof \PhpParser\Node\Expr) {
                return null;
            }
            /** @var Expr $compareExpr */
            $truthyExpr = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($truthyExpr, $compareExpr);
        }
        return $truthyExpr;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function createTruthyFromUnionType(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr, bool $treatAsNonEmpty)
    {
        $unionType = \PHPStan\Type\TypeCombinator::removeNull($unionType);
        if ($unionType instanceof \PHPStan\Type\UnionType) {
            $compareExprs = [];
            foreach ($unionType->getTypes() as $unionedType) {
                $compareExprs[] = $this->createIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty);
            }
            /** @var Expr $truthyExpr */
            $truthyExpr = \array_shift($compareExprs);
            foreach ($compareExprs as $compareExpr) {
                /** @var Expr $compareExpr */
                $truthyExpr = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($truthyExpr, $compareExpr);
            }
            return $truthyExpr;
        }
        if ($unionType instanceof \PHPStan\Type\BooleanType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createTrue());
        }
        if ($unionType instanceof \PHPStan\Type\TypeWithClassName) {
            $instanceOf = new \PhpParser\Node\Expr\Instanceof_($expr, new \PhpParser\Node\Name\FullyQualified($unionType->getClassName()));
            return new \PhpParser\Node\Expr\BooleanNot($instanceOf);
        }
        $toNullIdentical = new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createNull());
        // assume we have to check empty string, integer and bools
        if (!$treatAsNonEmpty) {
            $scalarFalsyIdentical = $this->createIdenticalFalsyCompare($unionType, $expr, $treatAsNonEmpty);
            if (!$scalarFalsyIdentical instanceof \PhpParser\Node\Expr) {
                return null;
            }
            return new \PhpParser\Node\Expr\BinaryOp\BooleanOr($toNullIdentical, $scalarFalsyIdentical);
        }
        return $toNullIdentical;
    }
}
