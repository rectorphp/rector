<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BinaryOp\LogicalXor;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\UnionType;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ExprAnalyzer
{
    public function isBoolExpr(Expr $expr) : bool
    {
        return $expr instanceof BooleanNot || $expr instanceof Empty_ || $expr instanceof Isset_ || $expr instanceof Instanceof_ || $expr instanceof Bool_ || $expr instanceof Equal || $expr instanceof NotEqual || $expr instanceof Identical || $expr instanceof NotIdentical || $expr instanceof Greater || $expr instanceof GreaterOrEqual || $expr instanceof Smaller || $expr instanceof SmallerOrEqual || $expr instanceof BooleanAnd || $expr instanceof BooleanOr || $expr instanceof LogicalAnd || $expr instanceof LogicalOr || $expr instanceof LogicalXor;
    }
    public function isCallLikeReturnNativeBool(Expr $expr) : bool
    {
        if (!$expr instanceof CallLike) {
            return \false;
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $nativeType = $scope->getNativeType($expr);
        return $nativeType->isBoolean()->yes();
    }
    /**
     * Verify that Expr has ->expr property that can be wrapped by parentheses
     */
    public function isExprWithExprPropertyWrappable(Node $node) : bool
    {
        if (!$node instanceof Expr) {
            return \false;
        }
        // ensure only verify on reprint, using token start verification is more reliable for its check
        if ($node->getStartTokenPos() > 0) {
            return \false;
        }
        if ($node instanceof Cast || $node instanceof YieldFrom || $node instanceof UnaryMinus || $node instanceof UnaryPlus || $node instanceof Throw_ || $node instanceof Empty_ || $node instanceof BooleanNot || $node instanceof Clone_ || $node instanceof ErrorSuppress || $node instanceof BitwiseNot || $node instanceof Eval_ || $node instanceof Print_ || $node instanceof Exit_ || $node instanceof Include_ || $node instanceof Instanceof_) {
            return $node->expr instanceof BinaryOp;
        }
        return \false;
    }
    public function isNonTypedFromParam(Expr $expr) : bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            // uncertainty when scope not yet filled/overlapped on just refactored
            return \true;
        }
        $nativeType = $scope->getNativeType($expr);
        $type = $scope->getType($expr);
        if ($nativeType instanceof MixedType && !$nativeType->isExplicitMixed() || $nativeType instanceof MixedType && !$type instanceof MixedType) {
            return \true;
        }
        if ($nativeType instanceof ObjectWithoutClassType && !$type instanceof ObjectWithoutClassType) {
            return \true;
        }
        if ($nativeType instanceof UnionType) {
            return !$nativeType->equals($type);
        }
        return !$nativeType->isSuperTypeOf($type)->yes();
    }
    public function isDynamicExpr(Expr $expr) : bool
    {
        // Unwrap UnaryPlus and UnaryMinus
        if ($expr instanceof UnaryPlus || $expr instanceof UnaryMinus) {
            $expr = $expr->expr;
        }
        if ($expr instanceof Array_) {
            return $this->isDynamicArray($expr);
        }
        if ($expr instanceof Scalar) {
            // string interpolation is true, otherwise false
            return $expr instanceof InterpolatedString;
        }
        return !$this->isAllowedConstFetchOrClassConstFetch($expr);
    }
    public function isDynamicArray(Array_ $array) : bool
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            if (!$this->isAllowedArrayKey($item->key)) {
                return \true;
            }
            if (!$this->isAllowedArrayValue($item->value)) {
                return \true;
            }
        }
        return \false;
    }
    private function isAllowedConstFetchOrClassConstFetch(Expr $expr) : bool
    {
        if ($expr instanceof ConstFetch) {
            return \true;
        }
        if ($expr instanceof ClassConstFetch) {
            if (!$expr->class instanceof Name) {
                return \false;
            }
            if (!$expr->name instanceof Identifier) {
                return \false;
            }
            // static::class cannot be used for compile-time class name resolution
            return $expr->class->toString() !== ObjectReference::STATIC;
        }
        return \false;
    }
    private function isAllowedArrayKey(?Expr $expr) : bool
    {
        if (!$expr instanceof Expr) {
            return \true;
        }
        if ($expr instanceof String_) {
            return \true;
        }
        return $expr instanceof Int_;
    }
    private function isAllowedArrayValue(Expr $expr) : bool
    {
        if ($expr instanceof Array_) {
            return !$this->isDynamicArray($expr);
        }
        return !$this->isDynamicExpr($expr);
    }
}
