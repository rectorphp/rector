<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
use Rector\Core\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ExprAnalyzer
{
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
        if ($nativeType instanceof MixedType && !$nativeType->isExplicitMixed()) {
            return \true;
        }
        $type = $scope->getType($expr);
        if ($nativeType instanceof UnionType) {
            return !$nativeType->equals($type);
        }
        return !$nativeType->isSuperTypeOf($type)->yes();
    }
    public function isDynamicExpr(Expr $expr) : bool
    {
        if (!$expr instanceof Array_) {
            if ($expr instanceof Scalar) {
                // string interpolation is true, otherwise false
                return $expr instanceof Encapsed;
            }
            return !$this->isAllowedConstFetchOrClassConstFetch($expr);
        }
        return $this->isDynamicArray($expr);
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
        return $expr instanceof LNumber;
    }
    private function isAllowedArrayValue(Expr $expr) : bool
    {
        if ($expr instanceof Array_) {
            return !$this->isDynamicArray($expr);
        }
        return !$this->isDynamicExpr($expr);
    }
}
