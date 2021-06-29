<?php

declare(strict_types=1);

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
    public function __construct(
        private TypeUnwrapper $typeUnwrapper,
        private ReflectionProvider $reflectionProvider,
        private NodeNameResolver $nodeNameResolver,
        private PropertyNaming $propertyNaming
    ) {
    }

    /**
     * @return MethodCall|PropertyFetch|Variable|null
     */
    public function resolveTypeProvidingExprFromClass(
        Class_ $class,
        ClassMethod | Function_ $functionLike,
        ObjectType $objectType
    ): ?Expr {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
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
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $propertyFetch = $this->resolvePropertyFetchProvidingType($classReflection, $scope, $objectType);
        if ($propertyFetch !== null) {
            return $propertyFetch;
        }

        // C. param in constructor?
        return $this->resolveConstructorParamProvidingType($functionLike, $objectType);
    }

    private function resolveMethodCallProvidingType(
        ClassReflection $classReflection,
        ObjectType $objectType
    ): ?MethodCall {
        foreach ($classReflection->getNativeMethods() as $methodReflection) {
            $functionVariant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            $returnType = $functionVariant->getReturnType();

            if (! $this->isMatchingType($returnType, $objectType)) {
                continue;
            }

            $thisVariable = new Variable('this');
            return new MethodCall($thisVariable, $methodReflection->getName());
        }

        return null;
    }

    private function resolvePropertyFetchProvidingType(
        ClassReflection $classReflection,
        Scope $scope,
        ObjectType $objectType
    ): ?PropertyFetch {
        $reflectionClass = $classReflection->getNativeReflection();

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            /** @var PhpPropertyReflection $phpPropertyReflection */
            $phpPropertyReflection = $classReflection->getProperty($reflectionProperty->getName(), $scope);

            $readableType = $phpPropertyReflection->getReadableType();
            if (! $this->isMatchingType($readableType, $objectType)) {
                continue;
            }

            return new PropertyFetch(new Variable('this'), $reflectionProperty->getName());
        }

        return null;
    }

    private function resolveConstructorParamProvidingType(FunctionLike $functionLike, ObjectType $objectType): ?Variable
    {
        if (! $functionLike instanceof ClassMethod) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($functionLike, MethodName::CONSTRUCT)) {
            return null;
        }

        $variableName = $this->propertyNaming->fqnToVariableName($objectType);
        return new Variable($variableName);
    }

    private function isMatchingType(Type $readableType, ObjectType $objectType): bool
    {
        if ($readableType instanceof MixedType) {
            return false;
        }

        $readableType = $this->typeUnwrapper->unwrapNullableType($readableType);

        if (! $readableType instanceof TypeWithClassName) {
            return false;
        }

        return $readableType->equals($objectType);
    }
}
