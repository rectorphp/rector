<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class NativeTypeClassTreeResolver
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private PrivatesAccessor $privatesAccessor
    ) {
    }

    public function resolveParameterReflectionType(
        ClassReflection $classReflection,
        string $methodName,
        int $position
    ): ?Type {
        $nativeReflectionClass = $classReflection->getNativeReflection();

        if (! $classReflection->hasNativeMethod($methodName)) {
            return null;
        }

        $phpstanParameterReflection = null;
        $methodReflection = $classReflection->getNativeMethod($methodName);
        foreach ($methodReflection->getVariants() as $parametersAcceptor) {
            $phpstanParameterReflection = $parametersAcceptor->getParameters()[$position] ?? null;
        }

        if (! $phpstanParameterReflection instanceof ParameterReflection) {
            return null;
        }

        $reflectionMethod = $nativeReflectionClass->getMethod($methodName);
        $parameterReflection = $reflectionMethod->getParameters()[$position] ?? null;
        if (! $parameterReflection instanceof \ReflectionParameter) {
            // no parameter found - e.g. when child class has an extra parameter with default value
            return null;
        }

        // "native" reflection from PHPStan removes the type, so we need to check with both reflection and php-paser
        $nativeType = $this->resolveNativeType($parameterReflection, $phpstanParameterReflection);
        if (! $nativeType instanceof MixedType) {
            return $nativeType;
        }

        return TypehintHelper::decideTypeFromReflection(
            $parameterReflection->getType(),
            null,
            $classReflection->getName(),
            $parameterReflection->isVariadic()
        );
    }

    private function resolveNativeType(
        \ReflectionParameter $reflectionParameter,
        ParameterReflection $parameterReflection
    ): Type {
        if (! $reflectionParameter instanceof ReflectionParameter) {
            return new MixedType();
        }

        $betterReflectionParameter = $this->privatesAccessor->getPrivateProperty(
            $reflectionParameter,
            'betterReflectionParameter'
        );

        $param = $this->privatesAccessor->getPrivateProperty($betterReflectionParameter, 'node');
        if (! $param instanceof Param) {
            return new MixedType();
        }

        if (! $param->type instanceof Node) {
            return new MixedType();
        }

        $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        return $this->joinWithNullTypeIfNullDefaultValue($parameterReflection, $paramType);
    }

    private function joinWithNullTypeIfNullDefaultValue(ParameterReflection $parameterReflection, Type $paramType): Type
    {
        // nullable type!
        if (! $parameterReflection->getDefaultValue() instanceof NullType) {
            return $paramType;
        }

        if (TypeCombinator::containsNull($paramType)) {
            return $paramType;
        }

        return TypeCombinator::addNull($paramType);
    }
}
