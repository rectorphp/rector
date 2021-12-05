<?php

declare(strict_types=1);

namespace Rector\Php71\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CountableAnalyzer
{
    public function __construct(
        private readonly NodeTypeResolver $nodeTypeResolver,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer
    ) {
    }

    public function isCastableArrayType(Expr $expr, ArrayType $arrayType): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if ($arrayType instanceof ConstantArrayType) {
            return false;
        }

        $callerObjectType = $this->nodeTypeResolver->getType($expr->var);

        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if (! is_string($propertyName)) {
            return false;
        }

        if ($callerObjectType instanceof UnionType) {
            $callerObjectType = $callerObjectType->getTypes()[0];
        }

        if (! $callerObjectType instanceof TypeWithClassName) {
            return false;
        }

        if (is_a($callerObjectType->getClassName(), Stmt::class, true)) {
            return false;
        }

        if (is_a($callerObjectType->getClassName(), Array_::class, true)) {
            return false;
        }

        // this must be handled reflection, as PHPStan ReflectionProvider does not provide default values for properties in any way

        $classReflection = $this->reflectionProvider->getClass($callerObjectType->getClassName());

        $nativeReflectionClass = $classReflection->getNativeReflection();
        $propertiesDefaults = $nativeReflectionClass->getDefaultProperties();

        if (! array_key_exists($propertyName, $propertiesDefaults)) {
            return false;
        }

        $phpPropertyReflection = $this->resolveProperty($expr, $classReflection, $propertyName);
        if (! $phpPropertyReflection instanceof PhpPropertyReflection) {
            return false;
        }

        $nativeType = $phpPropertyReflection->getNativeType();
        if ($this->isIterableOrFilledByConstructParam($nativeType, $expr)) {
            return false;
        }

        $propertyDefaultValue = $propertiesDefaults[$propertyName];
        return $propertyDefaultValue === null;
    }

    private function isIterableOrFilledByConstructParam(Type $nativeType, PropertyFetch $propertyFetch): bool
    {
        if ($nativeType->isIterable()->yes()) {
            return true;
        }

        return $this->propertyFetchAnalyzer->isFilledByConstructParam($propertyFetch);
    }

    private function resolveProperty(
        PropertyFetch $propertyFetch,
        ClassReflection $classReflection,
        string $propertyName
    ): ?PropertyReflection {
        $scope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        return $classReflection->getProperty($propertyName, $scope);
    }
}
