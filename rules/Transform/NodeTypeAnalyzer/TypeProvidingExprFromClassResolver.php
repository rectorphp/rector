<?php

declare (strict_types=1);
namespace Rector\Transform\NodeTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
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
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(TypeUnwrapper $typeUnwrapper, ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, PropertyNaming $propertyNaming)
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
    public function resolveTypeProvidingExprFromClass(Class_ $class, $functionLike, ObjectType $objectType) : ?Expr
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        // A. match a method
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodCallProvidingType = $this->resolveMethodCallProvidingType($classReflection, $objectType);
        if ($methodCallProvidingType !== null) {
            return $methodCallProvidingType;
        }
        // B. match existing property
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $propertyFetch = $this->resolvePropertyFetchProvidingType($classReflection, $scope, $objectType);
        if ($propertyFetch !== null) {
            return $propertyFetch;
        }
        // C. param in constructor?
        return $this->resolveConstructorParamProvidingType($functionLike, $objectType);
    }
    private function resolveMethodCallProvidingType(ClassReflection $classReflection, ObjectType $objectType) : ?MethodCall
    {
        $methodReflections = $this->getClassMethodReflections($classReflection);
        foreach ($methodReflections as $methodReflection) {
            $functionVariant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            $returnType = $functionVariant->getReturnType();
            if (!$this->isMatchingType($returnType, $objectType)) {
                continue;
            }
            $thisVariable = new Variable('this');
            return new MethodCall($thisVariable, $methodReflection->getName());
        }
        return null;
    }
    private function resolvePropertyFetchProvidingType(ClassReflection $classReflection, Scope $scope, ObjectType $objectType) : ?PropertyFetch
    {
        $nativeReflectionClass = $classReflection->getNativeReflection();
        foreach ($nativeReflectionClass->getProperties() as $reflectionProperty) {
            /** @var PhpPropertyReflection $phpPropertyReflection */
            $phpPropertyReflection = $classReflection->getProperty($reflectionProperty->getName(), $scope);
            $readableType = $phpPropertyReflection->getReadableType();
            if (!$this->isMatchingType($readableType, $objectType)) {
                continue;
            }
            return new PropertyFetch(new Variable('this'), $reflectionProperty->getName());
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function resolveConstructorParamProvidingType($functionLike, ObjectType $objectType) : ?Variable
    {
        if (!$functionLike instanceof ClassMethod) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($functionLike, MethodName::CONSTRUCT)) {
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
        $readableType = $this->typeUnwrapper->unwrapNullableType($readableType);
        if (!$readableType instanceof TypeWithClassName) {
            return \false;
        }
        return $readableType->equals($objectType);
    }
    /**
     * @return MethodReflection[]
     */
    private function getClassMethodReflections(ClassReflection $classReflection) : array
    {
        $nativeClassReflection = $classReflection->getNativeReflection();
        $methodReflections = [];
        foreach ($nativeClassReflection->getMethods() as $reflectionMethod) {
            $methodReflections[] = $classReflection->getNativeMethod($reflectionMethod->getName());
        }
        return $methodReflections;
    }
}
