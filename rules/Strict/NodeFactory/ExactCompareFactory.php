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
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\NodeFactory;
final class ExactCompareFactory
{
    /**
     * @readonly
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
        if ($exprType instanceof \PHPStan\Type\NullType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createNull());
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
        return $this->resolveTruthyExpr($compareExprs);
    }
    /**
     * @return array<Expr|null>
     */
    private function collectCompareExprs(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr, bool $treatAsNonEmpty) : array
    {
        $compareExprs = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $compareExprs[] = $this->createIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty);
        }
        return $compareExprs;
    }
    private function cleanUpPossibleNullableUnionType(\PHPStan\Type\UnionType $unionType) : \PHPStan\Type\Type
    {
        return \count($unionType->getTypes()) === 2 ? \PHPStan\Type\TypeCombinator::removeNull($unionType) : $unionType;
    }
    /**
     * @param array<Expr|null> $compareExprs
     */
    private function resolveTruthyExpr(array $compareExprs) : ?\PhpParser\Node\Expr
    {
        $truthyExpr = \array_shift($compareExprs);
        foreach ($compareExprs as $compareExpr) {
            if (!$compareExpr instanceof \PhpParser\Node\Expr) {
                return null;
            }
            if (!$truthyExpr instanceof \PhpParser\Node\Expr) {
                return null;
            }
            $truthyExpr = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($truthyExpr, $compareExpr);
        }
        return $truthyExpr;
    }
    /**
     * @return \PhpParser\Node\Expr|null
     */
    private function createTruthyFromUnionType(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr, bool $treatAsNonEmpty)
    {
        $unionType = $this->cleanUpPossibleNullableUnionType($unionType);
        if ($unionType instanceof \PHPStan\Type\UnionType) {
            $compareExprs = $this->collectCompareExprs($unionType, $expr, $treatAsNonEmpty);
            return $this->resolveTruthyExpr($compareExprs);
        }
        if ($unionType instanceof \PHPStan\Type\BooleanType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createTrue());
        }
        if ($unionType instanceof \PHPStan\Type\TypeWithClassName) {
            $instanceOf = new \PhpParser\Node\Expr\Instanceof_($expr, new \PhpParser\Node\Name\FullyQualified($unionType->getClassName()));
            return new \PhpParser\Node\Expr\BooleanNot($instanceOf);
        }
        $toNullIdentical = new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $this->nodeFactory->createNull());
        if ($treatAsNonEmpty) {
            return $toNullIdentical;
        }
        // assume we have to check empty string, integer and bools
        $scalarFalsyIdentical = $this->createIdenticalFalsyCompare($unionType, $expr, $treatAsNonEmpty);
        if (!$scalarFalsyIdentical instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\BooleanOr($toNullIdentical, $scalarFalsyIdentical);
    }
}
