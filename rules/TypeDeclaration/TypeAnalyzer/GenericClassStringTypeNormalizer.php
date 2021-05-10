<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
final class GenericClassStringTypeNormalizer
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function normalize(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, $callback) : Type {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $callback($type);
            }
            // skip string that look like classe
            if ($type->getValue() === 'error') {
                return $callback($type);
            }
            if (!$this->reflectionProvider->hasClass($type->getValue())) {
                return $callback($type);
            }
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($type->getValue()));
        });
    }
}
