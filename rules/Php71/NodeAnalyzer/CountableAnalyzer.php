<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function isCastableArrayType(\PhpParser\Node\Expr $expr, \PHPStan\Type\ArrayType $arrayType) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if ($arrayType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return \false;
        }
        $callerObjectType = $this->nodeTypeResolver->getType($expr->var);
        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if (!\is_string($propertyName)) {
            return \false;
        }
        if ($callerObjectType instanceof \PHPStan\Type\UnionType) {
            $callerObjectType = $callerObjectType->getTypes()[0];
        }
        if (!$callerObjectType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if ($this->isCallerObjectClassNameStmtOrArray($callerObjectType)) {
            return \false;
        }
        // this must be handled reflection, as PHPStan ReflectionProvider does not provide default values for properties in any way
        $classReflection = $this->reflectionProvider->getClass($callerObjectType->getClassName());
        $nativeReflectionClass = $classReflection->getNativeReflection();
        $propertiesDefaults = $nativeReflectionClass->getDefaultProperties();
        if (!\array_key_exists($propertyName, $propertiesDefaults)) {
            return \false;
        }
        $phpPropertyReflection = $this->resolveProperty($expr, $classReflection, $propertyName);
        if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
            return \false;
        }
        $nativeType = $phpPropertyReflection->getNativeType();
        if ($this->isIterableOrFilledByConstructParam($nativeType, $expr)) {
            return \false;
        }
        if ($this->propertyFetchAnalyzer->isFilledViaMethodCallInConstructStmts($expr)) {
            return \false;
        }
        $propertyDefaultValue = $propertiesDefaults[$propertyName];
        return $propertyDefaultValue === null;
    }
    private function isCallerObjectClassNameStmtOrArray(\PHPStan\Type\TypeWithClassName $typeWithClassName) : bool
    {
        if (\is_a($typeWithClassName->getClassName(), \PhpParser\Node\Stmt::class, \true)) {
            return \true;
        }
        return \is_a($typeWithClassName->getClassName(), \PhpParser\Node\Expr\Array_::class, \true);
    }
    private function isIterableOrFilledByConstructParam(\PHPStan\Type\Type $nativeType, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : bool
    {
        if ($nativeType->isIterable()->yes()) {
            return \true;
        }
        return $this->propertyFetchAnalyzer->isFilledByConstructParam($propertyFetch);
    }
    private function resolveProperty(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, \PHPStan\Reflection\ClassReflection $classReflection, string $propertyName) : ?\PHPStan\Reflection\PropertyReflection
    {
        $scope = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        return $classReflection->getProperty($propertyName, $scope);
    }
}
