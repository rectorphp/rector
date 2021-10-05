<?php

declare (strict_types=1);
namespace Rector\Strict\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
final class ExactCompareFactory
{
    public function createIdenticalFalsyCompare(\PHPStan\Type\Type $exprType, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\BinaryOp\Identical
    {
        if ($exprType instanceof \PHPStan\Type\StringType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Scalar\String_(''));
        }
        if ($exprType instanceof \PHPStan\Type\IntegerType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Scalar\LNumber(0));
        }
        if ($exprType instanceof \PHPStan\Type\ArrayType) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Expr\Array_([]));
        }
        if (!$exprType instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        if (!\PHPStan\Type\TypeCombinator::containsNull($exprType)) {
            return null;
        }
        return $this->createTruthyFromUnionType($exprType, $expr);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|null
     */
    public function createNotIdenticalFalsyCompare(\PHPStan\Type\Type $exprType, \PhpParser\Node\Expr $expr)
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
        if (!\PHPStan\Type\TypeCombinator::containsNull($exprType)) {
            return null;
        }
        return $this->createFromUnionType($exprType, $expr);
    }
    /**
     * @param \PHPStan\Type\Type|\PHPStan\Type\UnionType $exprType
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function createFromUnionType($exprType, \PhpParser\Node\Expr $expr)
    {
        $exprType = \PHPStan\Type\TypeCombinator::removeNull($exprType);
        if ($exprType instanceof \PHPStan\Type\BooleanType) {
            $trueConstFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true'));
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $trueConstFetch);
        }
        if ($exprType instanceof \PHPStan\Type\TypeWithClassName) {
            return new \PhpParser\Node\Expr\Instanceof_($expr, new \PhpParser\Node\Name\FullyQualified($exprType->getClassName()));
        }
        $nullConstFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'));
        return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, $nullConstFetch);
    }
    private function resolveFalsyTypesCount(\PHPStan\Type\UnionType $unionType) : int
    {
        $falsyTypesCount = 0;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\StringType) {
                ++$falsyTypesCount;
            }
            if ($unionedType instanceof \PHPStan\Type\IntegerType) {
                ++$falsyTypesCount;
            }
            if ($unionedType instanceof \PHPStan\Type\FloatType) {
                ++$falsyTypesCount;
            }
            if ($unionedType instanceof \PHPStan\Type\ArrayType) {
                ++$falsyTypesCount;
            }
        }
        return $falsyTypesCount;
    }
    private function createTruthyFromUnionType(\PHPStan\Type\UnionType $unionType, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\BinaryOp\Identical
    {
        $unionType = \PHPStan\Type\TypeCombinator::removeNull($unionType);
        if ($unionType instanceof \PHPStan\Type\BooleanType) {
            $trueConstFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true'));
            return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $trueConstFetch);
        }
        if ($unionType instanceof \PHPStan\Type\UnionType) {
            $falsyTypesCount = $this->resolveFalsyTypesCount($unionType);
            // impossible to refactor to string value compare, as many falsy values can be provided
            if ($falsyTypesCount > 1) {
                return null;
            }
        }
        $nullConstFetch = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'));
        return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, $nullConstFetch);
    }
}
