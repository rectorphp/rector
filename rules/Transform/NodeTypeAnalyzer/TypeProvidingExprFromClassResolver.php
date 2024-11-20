<?php

declare (strict_types=1);
namespace Rector\Transform\NodeTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Rector\ValueObject\MethodName;
final class TypeProvidingExprFromClassResolver
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    public function __construct(ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, PropertyNaming $propertyNaming)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyNaming = $propertyNaming;
    }
    /**
     * @return MethodCall|PropertyFetch|Variable|null
     */
    public function resolveTypeProvidingExprFromClass(Class_ $class, ClassMethod $classMethod, ObjectType $objectType) : ?Expr
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        // A. match a method
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodCallProvidingType = $this->resolveMethodCallProvidingType($classReflection, $objectType);
        if ($methodCallProvidingType instanceof MethodCall) {
            return $methodCallProvidingType;
        }
        // B. match existing property
        $propertyFetch = $this->resolvePropertyFetchProvidingType($classReflection, $objectType);
        if ($propertyFetch instanceof PropertyFetch) {
            return $propertyFetch;
        }
        // C. param in constructor?
        return $this->resolveConstructorParamProvidingType($classMethod, $objectType);
    }
    private function resolveMethodCallProvidingType(ClassReflection $classReflection, ObjectType $objectType) : ?MethodCall
    {
        $methodReflections = $this->getClassMethodReflections($classReflection);
        foreach ($methodReflections as $methodReflection) {
            $functionVariant = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
            $returnType = $functionVariant->getReturnType();
            if (!$this->isMatchingType($returnType, $objectType)) {
                continue;
            }
            $thisVariable = new Variable('this');
            return new MethodCall($thisVariable, $methodReflection->getName());
        }
        return null;
    }
    private function resolvePropertyFetchProvidingType(ClassReflection $classReflection, ObjectType $objectType) : ?PropertyFetch
    {
        $nativeReflectionClass = $classReflection->getNativeReflection();
        foreach ($nativeReflectionClass->getProperties() as $reflectionProperty) {
            /** @var PhpPropertyReflection $phpPropertyReflection */
            $phpPropertyReflection = $classReflection->getNativeProperty($reflectionProperty->getName());
            $readableType = $phpPropertyReflection->getReadableType();
            if (!$this->isMatchingType($readableType, $objectType)) {
                continue;
            }
            return new PropertyFetch(new Variable('this'), $reflectionProperty->getName());
        }
        return null;
    }
    private function resolveConstructorParamProvidingType(ClassMethod $classMethod, ObjectType $objectType) : ?Variable
    {
        if (!$this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return null;
        }
        $variableName = $this->propertyNaming->fqnToVariableName($objectType);
        return new Variable($variableName);
    }
    private function isMatchingType(Type $readableType, ObjectType $objectType) : bool
    {
        if ($readableType instanceof MixedType) {
            return \false;
        }
        $readableType = TypeCombinator::removeNull($readableType);
        $className = ClassNameFromObjectTypeResolver::resolve($readableType);
        if ($className === null) {
            return \false;
        }
        return $readableType->equals($objectType);
    }
    /**
     * @return MethodReflection[]
     */
    private function getClassMethodReflections(ClassReflection $classReflection) : array
    {
        $nativeReflection = $classReflection->getNativeReflection();
        $methodReflections = [];
        foreach ($nativeReflection->getMethods() as $reflectionMethod) {
            $methodReflections[] = $classReflection->getNativeMethod($reflectionMethod->getName());
        }
        return $methodReflections;
    }
}
