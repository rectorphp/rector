<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use RectorPrefix20210726\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class GenericClassStringTypeNormalizer
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20210726\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->parameterProvider = $parameterProvider;
    }
    public function normalize(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, $callback) : Type {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $callback($type);
            }
            $value = $type->getValue();
            // skip string that look like classe
            if ($value === 'error') {
                return $callback($type);
            }
            if (!$this->reflectionProvider->hasClass($value)) {
                return $callback($type);
            }
            return $this->resolveStringType($value);
        });
    }
    /**
     * @return \PHPStan\Type\Generic\GenericClassStringType|\PHPStan\Type\StringType
     */
    private function resolveStringType(string $value)
    {
        $classReflection = $this->reflectionProvider->getClass($value);
        if ($classReflection->isBuiltIn()) {
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($value));
        }
        if (\strpos($value, '\\') !== \false) {
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($value));
        }
        return new \PHPStan\Type\StringType();
    }
}
