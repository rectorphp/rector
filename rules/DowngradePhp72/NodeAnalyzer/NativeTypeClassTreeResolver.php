<?php

declare (strict_types=1);
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
use RectorPrefix20210704\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class NativeTypeClassTreeResolver
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20210704\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->privatesAccessor = $privatesAccessor;
    }
    public function resolveParameterReflectionType(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName, int $position) : ?\PHPStan\Type\Type
    {
        $nativeReflectionClass = $classReflection->getNativeReflection();
        if (!$classReflection->hasNativeMethod($methodName)) {
            return null;
        }
        $phpstanParameterReflection = null;
        $methodReflection = $classReflection->getNativeMethod($methodName);
        foreach ($methodReflection->getVariants() as $parametersAcceptor) {
            $phpstanParameterReflection = $parametersAcceptor->getParameters()[$position] ?? null;
        }
        if (!$phpstanParameterReflection instanceof \PHPStan\Reflection\ParameterReflection) {
            return null;
        }
        $reflectionMethod = $nativeReflectionClass->getMethod($methodName);
        $parameterReflection = $reflectionMethod->getParameters()[$position] ?? null;
        if (!$parameterReflection instanceof \ReflectionParameter) {
            // no parameter found - e.g. when child class has an extra parameter with default value
            return null;
        }
        // "native" reflection from PHPStan removes the type, so we need to check with both reflection and php-paser
        $nativeType = $this->resolveNativeType($parameterReflection, $phpstanParameterReflection);
        if (!$nativeType instanceof \PHPStan\Type\MixedType) {
            return $nativeType;
        }
        return \PHPStan\Type\TypehintHelper::decideTypeFromReflection($parameterReflection->getType(), null, $classReflection->getName(), $parameterReflection->isVariadic());
    }
    private function resolveNativeType(\ReflectionParameter $reflectionParameter, \PHPStan\Reflection\ParameterReflection $parameterReflection) : \PHPStan\Type\Type
    {
        if (!$reflectionParameter instanceof \PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter) {
            return new \PHPStan\Type\MixedType();
        }
        $betterReflectionParameter = $this->privatesAccessor->getPrivateProperty($reflectionParameter, 'betterReflectionParameter');
        $param = $this->privatesAccessor->getPrivateProperty($betterReflectionParameter, 'node');
        if (!$param instanceof \PhpParser\Node\Param) {
            return new \PHPStan\Type\MixedType();
        }
        if (!$param->type instanceof \PhpParser\Node) {
            return new \PHPStan\Type\MixedType();
        }
        $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        return $this->joinWithNullTypeIfNullDefaultValue($parameterReflection, $paramType);
    }
    private function joinWithNullTypeIfNullDefaultValue(\PHPStan\Reflection\ParameterReflection $parameterReflection, \PHPStan\Type\Type $paramType) : \PHPStan\Type\Type
    {
        // nullable type!
        if (!$parameterReflection->getDefaultValue() instanceof \PHPStan\Type\NullType) {
            return $paramType;
        }
        if (\PHPStan\Type\TypeCombinator::containsNull($paramType)) {
            return $paramType;
        }
        return \PHPStan\Type\TypeCombinator::addNull($paramType);
    }
}
