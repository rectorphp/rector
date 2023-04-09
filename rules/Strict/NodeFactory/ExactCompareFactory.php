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
use PHPStan\Type\NullType;
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
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|null
     */
    public function createIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNonEmpty)
    {
        if ($exprType->isString()->yes()) {
            return new Identical($expr, new String_(''));
        }
        if ($exprType->isInteger()->yes()) {
            return new Identical($expr, new LNumber(0));
        }
        if ($exprType->isBoolean()->yes()) {
            return new Identical($expr, $this->nodeFactory->createFalse());
        }
        if ($exprType->isArray()->yes()) {
            return new Identical($expr, new Array_([]));
        }
        if ($exprType instanceof NullType) {
            return new Identical($expr, $this->nodeFactory->createNull());
        }
        if (!$exprType instanceof UnionType) {
            return null;
        }
        return $this->createTruthyFromUnionType($exprType, $expr, $treatAsNonEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    public function createNotIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNotEmpty)
    {
        if ($exprType->isString()->yes()) {
            return new NotIdentical($expr, new String_(''));
        }
        if ($exprType->isInteger()->yes()) {
            return new NotIdentical($expr, new LNumber(0));
        }
        if ($exprType->isArray()->yes()) {
            return new NotIdentical($expr, new Array_([]));
        }
        if (!$exprType instanceof UnionType) {
            return null;
        }
        return $this->createFromUnionType($exprType, $expr, $treatAsNotEmpty);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function createFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNotEmpty)
    {
        $unionType = TypeCombinator::removeNull($unionType);
        if ($unionType->isBoolean()->yes()) {
            return new Identical($expr, $this->nodeFactory->createTrue());
        }
        if ($unionType instanceof TypeWithClassName) {
            return new Instanceof_($expr, new FullyQualified($unionType->getClassName()));
        }
        $nullConstFetch = $this->nodeFactory->createNull();
        $toNullNotIdentical = new NotIdentical($expr, $nullConstFetch);
        if ($unionType instanceof UnionType) {
            return $this->resolveFromCleanedNullUnionType($unionType, $expr, $treatAsNotEmpty);
        }
        $compareExpr = $this->createNotIdenticalFalsyCompare($unionType, $expr, $treatAsNotEmpty);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        return new BooleanAnd($toNullNotIdentical, $compareExpr);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function resolveFromCleanedNullUnionType(UnionType $unionType, Expr $expr, bool $treatAsNotEmpty)
    {
        $compareExprs = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $compareExprs[] = $this->createNotIdenticalFalsyCompare($unionedType, $expr, $treatAsNotEmpty);
        }
        return $this->createBooleanAnd($compareExprs);
    }
    /**
     * @return array<Identical|BooleanOr|NotIdentical|BooleanNot|Instanceof_|BooleanAnd|null>
     */
    private function collectCompareExprs(UnionType $unionType, Expr $expr, bool $treatAsNonEmpty) : array
    {
        $compareExprs = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $compareExprs[] = $this->createIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty);
        }
        return $compareExprs;
    }
    private function cleanUpPossibleNullableUnionType(UnionType $unionType) : Type
    {
        return \count($unionType->getTypes()) === 2 ? TypeCombinator::removeNull($unionType) : $unionType;
    }
    /**
     * @param array<Identical|BooleanOr|NotIdentical|BooleanAnd|Instanceof_|BooleanNot|null> $compareExprs
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function createBooleanOr(array $compareExprs)
    {
        $truthyExpr = \array_shift($compareExprs);
        foreach ($compareExprs as $compareExpr) {
            if (!$compareExpr instanceof Expr) {
                return null;
            }
            if (!$truthyExpr instanceof Expr) {
                return null;
            }
            $truthyExpr = new BooleanOr($truthyExpr, $compareExpr);
        }
        return $truthyExpr;
    }
    /**
     * @param array<Identical|BooleanOr|NotIdentical|BooleanAnd|BooleanNot|Instanceof_|null> $compareExprs
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function createBooleanAnd(array $compareExprs)
    {
        $truthyExpr = \array_shift($compareExprs);
        foreach ($compareExprs as $compareExpr) {
            if (!$compareExpr instanceof Expr) {
                return null;
            }
            if (!$truthyExpr instanceof Expr) {
                return null;
            }
            $truthyExpr = new BooleanAnd($truthyExpr, $compareExpr);
        }
        return $truthyExpr;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|null
     */
    private function createTruthyFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNonEmpty)
    {
        $unionType = $this->cleanUpPossibleNullableUnionType($unionType);
        if ($unionType instanceof UnionType) {
            $compareExprs = $this->collectCompareExprs($unionType, $expr, $treatAsNonEmpty);
            return $this->createBooleanOr($compareExprs);
        }
        if ($unionType->isBoolean()->yes()) {
            return new NotIdentical($expr, $this->nodeFactory->createTrue());
        }
        if ($unionType instanceof TypeWithClassName) {
            return new BooleanNot(new Instanceof_($expr, new FullyQualified($unionType->getClassName())));
        }
        $toNullIdentical = new Identical($expr, $this->nodeFactory->createNull());
        if ($treatAsNonEmpty) {
            return $toNullIdentical;
        }
        // assume we have to check empty string, integer and bools
        $scalarFalsyIdentical = $this->createIdenticalFalsyCompare($unionType, $expr, $treatAsNonEmpty);
        if (!$scalarFalsyIdentical instanceof Expr) {
            return null;
        }
        return new BooleanOr($toNullIdentical, $scalarFalsyIdentical);
    }
}
