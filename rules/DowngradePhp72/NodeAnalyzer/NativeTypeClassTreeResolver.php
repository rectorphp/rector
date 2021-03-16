<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReflectionNamedType;
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

        $nativeMethodReflection = $nativeReflectionClass->getMethod($methodName);
        $parameterReflection = $nativeMethodReflection->getParameters()[$position] ?? null;
        if ($parameterReflection === null) {
            return new MixedType();
        }

        // "native" reflection from PHPStan removes the type, so we need to check with both reflection and php-paser
        $nativeType = $this->resolveNativeType($parameterReflection);
        if (! $nativeType instanceof MixedType) {
            return $nativeType;
        }

        if (! $parameterReflection->getType() instanceof ReflectionNamedType) {
            return new MixedType();
        }

        $typeName = (string) $parameterReflection->getType();
        if ($typeName === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if ($typeName === 'string') {
            return new StringType();
        }

        if ($typeName === 'bool') {
            return new BooleanType();
        }

        if ($typeName === 'int') {
            return new IntegerType();
        }

        if ($typeName === 'float') {
            return new FloatType();
        }

        throw new NotImplementedYetException();
    }

    private function resolveNativeType(\ReflectionParameter $parameterReflection): Type
    {
        if (! $parameterReflection instanceof ReflectionParameter) {
            return new MixedType();
        }

        $betterReflectionParameter = $this->privatesAccessor->getPrivateProperty(
            $parameterReflection,
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
