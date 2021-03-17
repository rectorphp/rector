<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class NativeTypeClassTreeResolver
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct(StaticTypeMapper $staticTypeMapper, PrivatesAccessor $privatesAccessor)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->privatesAccessor = $privatesAccessor;
    }

    public function resolveParameterReflectionType(
        ClassReflection $classReflection,
        string $methodName,
        int $position
    ): Type {
        $nativeReflectionClass = $classReflection->getNativeReflection();
        if (! $nativeReflectionClass->hasMethod($methodName)) {
            return new MixedType();
        }

        $reflectionMethod = $nativeReflectionClass->getMethod($methodName);
        $parameterReflection = $reflectionMethod->getParameters()[$position] ?? null;
        if (! $parameterReflection instanceof \ReflectionParameter) {
            return new MixedType();
        }

        // "native" reflection from PHPStan removes the type, so we need to check with both reflection and php-paser
        $nativeType = $this->resolveNativeType($parameterReflection);
        if (! $nativeType instanceof MixedType) {
            return $nativeType;
        }

<<<<<<< HEAD
        return TypehintHelper::decideTypeFromReflection(
            $parameterReflection->getType(),
            null,
            $classReflection->getName()
        );
=======
        $classReflection = $parameterReflection->getClass();
        if ($classReflection instanceof ClassReflection) {
            $className = $classReflection->getName();
        } else {
            $className = null;
        }

        return TypehintHelper::decideTypeFromReflection($parameterReflection->getType(), null, $className);
>>>>>>> 0a073fac41... skip parent type for construtc
    }

    private function resolveNativeType(\ReflectionParameter $reflectionParameter): Type
    {
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

        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
}
