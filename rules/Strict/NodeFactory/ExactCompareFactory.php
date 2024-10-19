<?php

declare (strict_types=1);
namespace Rector\Strict\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PhpParser\Node\NodeFactory;
final class ExactCompareFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|null|\PhpParser\Node\Expr\FuncCall
     */
    public function createIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNonEmpty, bool $isOnlyString = \true)
    {
        $result = null;
        if ($exprType->isString()->yes()) {
            if ($treatAsNonEmpty || !$isOnlyString) {
                return new Identical($expr, new String_(''));
            }
            $result = new BooleanOr(new Identical($expr, new String_('')), new Identical($expr, new String_('0')));
        } elseif ($exprType->isInteger()->yes()) {
            return new Identical($expr, new LNumber(0));
        } elseif ($exprType->isBoolean()->yes()) {
            return new Identical($expr, $this->nodeFactory->createFalse());
        } elseif ($exprType->isArray()->yes()) {
            return new Identical($expr, new Array_([]));
        } elseif ($exprType instanceof NullType) {
            return new Identical($expr, $this->nodeFactory->createNull());
        } elseif (!$exprType instanceof UnionType) {
            return null;
        } else {
            $result = $this->createTruthyFromUnionType($exprType, $expr, $treatAsNonEmpty, \false);
        }
        if ($result instanceof BooleanOr && $expr instanceof CallLike && $result->left instanceof Identical && $result->right instanceof Identical) {
            return new FuncCall(new Name('in_array'), [new Arg($expr), new Arg(new Array_([new ArrayItem($result->left->right), new ArrayItem($result->right->right)])), new Arg(new ConstFetch(new Name('true')))]);
        }
        return $result;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BooleanNot|null
     */
    public function createNotIdenticalFalsyCompare(Type $exprType, Expr $expr, bool $treatAsNotEmpty, bool $isOnlyString = \true)
    {
        $result = null;
        if ($exprType->isString()->yes()) {
            if ($treatAsNotEmpty || !$isOnlyString) {
                return new NotIdentical($expr, new String_(''));
            }
            $result = new BooleanAnd(new NotIdentical($expr, new String_('')), new NotIdentical($expr, new String_('0')));
        } elseif ($exprType->isInteger()->yes()) {
            return new NotIdentical($expr, new LNumber(0));
        } elseif ($exprType->isArray()->yes()) {
            return new NotIdentical($expr, new Array_([]));
        } elseif (!$exprType instanceof UnionType) {
            return null;
        } else {
            $result = $this->createFromUnionType($exprType, $expr, $treatAsNotEmpty, \false);
        }
        if ($result instanceof BooleanAnd && $expr instanceof CallLike && $result->left instanceof NotIdentical && $result->right instanceof NotIdentical) {
            return new BooleanNot(new FuncCall(new Name('in_array'), [new Arg($expr), new Arg(new Array_([new ArrayItem($result->left->right), new ArrayItem($result->right->right)])), new Arg(new ConstFetch(new Name('true')))]));
        }
        return $result;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|null
     */
    private function createFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNotEmpty, bool $isOnlyString)
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
        $compareExpr = $this->createNotIdenticalFalsyCompare($unionType, $expr, $treatAsNotEmpty, $isOnlyString);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        if ($treatAsNotEmpty) {
            return new BooleanAnd($toNullNotIdentical, $compareExpr);
        }
        if ($unionType->isString()->yes()) {
            $booleanAnd = new BooleanAnd($toNullNotIdentical, $compareExpr);
            return new BooleanAnd($booleanAnd, new NotIdentical($expr, new String_('0')));
        }
        return new BooleanAnd($toNullNotIdentical, $compareExpr);
    }
    private function resolveFromCleanedNullUnionType(UnionType $unionType, Expr $expr, bool $treatAsNotEmpty) : ?BooleanAnd
    {
        $compareExprs = $this->collectCompareExprs($unionType, $expr, $treatAsNotEmpty, \false);
        return $this->createBooleanAnd($compareExprs);
    }
    /**
     * @return array<Identical|BooleanOr|NotIdentical|BooleanNot|Instanceof_|BooleanAnd|FuncCall|null>
     */
    private function collectCompareExprs(UnionType $unionType, Expr $expr, bool $treatAsNonEmpty, bool $identical = \true) : array
    {
        $compareExprs = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $compareExprs[] = $identical ? $this->createIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty) : $this->createNotIdenticalFalsyCompare($unionedType, $expr, $treatAsNonEmpty);
        }
        return \array_unique($compareExprs, \SORT_REGULAR);
    }
    private function cleanUpPossibleNullableUnionType(UnionType $unionType) : Type
    {
        return \count($unionType->getTypes()) === 2 ? TypeCombinator::removeNull($unionType) : $unionType;
    }
    /**
     * @param array<Identical|BooleanOr|NotIdentical|BooleanAnd|Instanceof_|BooleanNot|FuncCall|null> $compareExprs
     */
    private function createBooleanOr(array $compareExprs) : ?BooleanOr
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
        /** @var BooleanOr $truthyExpr */
        return $truthyExpr;
    }
    /**
     * @param array<Identical|BooleanOr|NotIdentical|BooleanAnd|BooleanNot|Instanceof_|FuncCall|null> $compareExprs
     */
    private function createBooleanAnd(array $compareExprs) : ?BooleanAnd
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
        /** @var BooleanAnd $truthyExpr */
        return $truthyExpr;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BooleanNot|null
     */
    private function createTruthyFromUnionType(UnionType $unionType, Expr $expr, bool $treatAsNonEmpty, bool $isOnlyString)
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
        $scalarFalsyIdentical = $this->createIdenticalFalsyCompare($unionType, $expr, $treatAsNonEmpty, $isOnlyString);
        if (!$scalarFalsyIdentical instanceof Expr) {
            return null;
        }
        if ($unionType->isString()->yes()) {
            $booleanOr = new BooleanOr($toNullIdentical, $scalarFalsyIdentical);
            return new BooleanOr($booleanOr, new Identical($expr, new String_('0')));
        }
        return new BooleanOr($toNullIdentical, $scalarFalsyIdentical);
    }
}
