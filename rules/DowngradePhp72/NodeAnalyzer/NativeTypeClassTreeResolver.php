<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20210514\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
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
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20210514\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->privatesAccessor = $privatesAccessor;
    }
    public function resolveParameterReflectionType(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName, int $position) : \PHPStan\Type\Type
    {
        $nativeReflectionClass = $classReflection->getNativeReflection();
        $reflectionMethod = $nativeReflectionClass->getMethod($methodName);
        $parameterReflection = $reflectionMethod->getParameters()[$position] ?? null;
        if (!$parameterReflection instanceof \ReflectionParameter) {
            return new \PHPStan\Type\MixedType();
        }
        // "native" reflection from PHPStan removes the type, so we need to check with both reflection and php-paser
        $nativeType = $this->resolveNativeType($parameterReflection);
        if (!$nativeType instanceof \PHPStan\Type\MixedType) {
            return $nativeType;
        }
        return \PHPStan\Type\TypehintHelper::decideTypeFromReflection($parameterReflection->getType(), null, $classReflection->getName(), $parameterReflection->isVariadic());
    }
    private function resolveNativeType(\ReflectionParameter $reflectionParameter) : \PHPStan\Type\Type
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
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
}
