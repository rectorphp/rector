<?php

declare(strict_types=1);

namespace Rector\Generic\NodeTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

final class TypeProvidingExprFromClassResolver
{
    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(TypeUnwrapper $typeUnwrapper, ReflectionProvider $reflectionProvider)
    {
        $this->typeUnwrapper = $typeUnwrapper;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @return MethodCall|PropertyFetch|null
     */
    public function resolveTypeProvidingExprFromClass(Class_ $class, string $type): ?Expr
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        // A. match a method
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodCallProvidingType = $this->resolveMethodCallProvidingType($classReflection, $type);
        if ($methodCallProvidingType !== null) {
            return $methodCallProvidingType;
        }

        // B. match existing property
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        return $this->resolvePropertyFetchProvidingType($classReflection, $scope, $type);
    }

    private function resolveMethodCallProvidingType(ClassReflection $classReflection, string $type): ?MethodCall
    {
        foreach ($classReflection->getNativeMethods() as $methodReflection) {
            $functionVariant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            $returnType = $functionVariant->getReturnType();

            if (! $this->isMatchingType($returnType, $type)) {
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
        string $type
    ): ?PropertyFetch {
        foreach ($classReflection->getNativeReflection()->getProperties() as $reflectionProperty) {
            /** @var PhpPropertyReflection $phpPropertyReflection */
            $phpPropertyReflection = $classReflection->getProperty($reflectionProperty->getName(), $scope);

            $readableType = $phpPropertyReflection->getReadableType();
            if (! $this->isMatchingType($readableType, $type)) {
                continue;
            }

            return new PropertyFetch(new Variable('this'), $reflectionProperty->getName());
        }

        return null;
    }

    private function isMatchingType(Type $readableType, string $type): bool
    {
        if ($readableType instanceof MixedType) {
            return false;
        }

        if ($readableType instanceof UnionType) {
            $readableType = $this->typeUnwrapper->unwrapNullableType($readableType);
        }

        if (! $readableType instanceof TypeWithClassName) {
            return false;
        }

        return $readableType->getClassName() === $type;
    }
}
