<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
final class ContainsTypeAnalyser
{
    /**
     * @param class-string[] $types
     */
    public function containsExprTypes(Expr $expr, Scope $scope, array $types) : bool
    {
        foreach ($types as $type) {
            if (!$this->containsExprType($expr, $scope, $type)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param class-string $type
     */
    public function containsExprType(Expr $expr, Scope $scope, string $type) : bool
    {
        $exprType = $scope->getType($expr);
        return $this->containsTypeExprType($exprType, $type);
    }
    /**
     * @param class-string $type
     */
    private function containsTypeExprType(Type $exprType, string $type) : bool
    {
        if ($exprType instanceof IntersectionType) {
            $intersectionedTypes = $exprType->getTypes();
            foreach ($intersectionedTypes as $intersectionedType) {
                if ($this->isExprTypeOfType($intersectionedType, $type)) {
                    return \true;
                }
            }
        }
        return $this->isExprTypeOfType($exprType, $type);
    }
    /**
     * @param class-string $class
     */
    private function isUnionTypeWithClass(Type $type, string $class) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        $unionedTypes = $type->getTypes();
        foreach ($unionedTypes as $unionedType) {
            if (!$unionedType instanceof TypeWithClassName) {
                continue;
            }
            if (\is_a($unionedType->getClassName(), $class, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param class-string $type
     */
    private function isArrayWithItemType(Type $propertyType, string $type) : bool
    {
        if (!$propertyType instanceof ArrayType) {
            return \false;
        }
        $arrayItemType = $propertyType->getItemType();
        if (!$arrayItemType instanceof TypeWithClassName) {
            return \false;
        }
        return \is_a($arrayItemType->getClassName(), $type, \true);
    }
    /**
     * @param class-string $type
     */
    private function isExprTypeOfType(Type $exprType, string $type) : bool
    {
        if ($exprType instanceof TypeWithClassName) {
            return \is_a($exprType->getClassName(), $type, \true);
        }
        if ($this->isUnionTypeWithClass($exprType, $type)) {
            return \true;
        }
        return $this->isArrayWithItemType($exprType, $type);
    }
}
