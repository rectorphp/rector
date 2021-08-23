<?php

declare (strict_types=1);
namespace Rector\Transform\NodeTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
final class TypeProvidingExprFromClassResolver
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->typeUnwrapper = $typeUnwrapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyNaming = $propertyNaming;
    }
    /**
     * @return MethodCall|PropertyFetch|Variable|null
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function resolveTypeProvidingExprFromClass(\PhpParser\Node\Stmt\Class_ $class, $functionLike, \PHPStan\Type\ObjectType $objectType) : ?\PhpParser\Node\Expr
    {
        $className = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }
        // A. match a method
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodCallProvidingType = $this->resolveMethodCallProvidingType($classReflection, $objectType);
        if ($methodCallProvidingType !== null) {
            return $methodCallProvidingType;
        }
        // B. match existing property
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $propertyFetch = $this->resolvePropertyFetchProvidingType($classReflection, $scope, $objectType);
        if ($propertyFetch !== null) {
            return $propertyFetch;
        }
        // C. param in constructor?
        return $this->resolveConstructorParamProvidingType($functionLike, $objectType);
    }
    private function resolveMethodCallProvidingType(\PHPStan\Reflection\ClassReflection $classReflection, \PHPStan\Type\ObjectType $objectType) : ?\PhpParser\Node\Expr\MethodCall
    {
        foreach ($classReflection->getNativeMethods() as $methodReflection) {
            $functionVariant = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            $returnType = $functionVariant->getReturnType();
            if (!$this->isMatchingType($returnType, $objectType)) {
                continue;
            }
            $thisVariable = new \PhpParser\Node\Expr\Variable('this');
            return new \PhpParser\Node\Expr\MethodCall($thisVariable, $methodReflection->getName());
        }
        return null;
    }
    private function resolvePropertyFetchProvidingType(\PHPStan\Reflection\ClassReflection $classReflection, \PHPStan\Analyser\Scope $scope, \PHPStan\Type\ObjectType $objectType) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        $reflectionClass = $classReflection->getNativeReflection();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            /** @var PhpPropertyReflection $phpPropertyReflection */
            $phpPropertyReflection = $classReflection->getProperty($reflectionProperty->getName(), $scope);
            $readableType = $phpPropertyReflection->getReadableType();
            if (!$this->isMatchingType($readableType, $objectType)) {
                continue;
            }
            return new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $reflectionProperty->getName());
        }
        return null;
    }
    private function resolveConstructorParamProvidingType(\PhpParser\Node\FunctionLike $functionLike, \PHPStan\Type\ObjectType $objectType) : ?\PhpParser\Node\Expr\Variable
    {
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($functionLike, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        $variableName = $this->propertyNaming->fqnToVariableName($objectType);
        return new \PhpParser\Node\Expr\Variable($variableName);
    }
    private function isMatchingType(\PHPStan\Type\Type $readableType, \PHPStan\Type\ObjectType $objectType) : bool
    {
        if ($readableType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        $readableType = $this->typeUnwrapper->unwrapNullableType($readableType);
        if (!$readableType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        return $readableType->equals($objectType);
    }
}
