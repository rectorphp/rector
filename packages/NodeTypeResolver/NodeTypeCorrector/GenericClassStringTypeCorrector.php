<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
final class GenericClassStringTypeCorrector
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function correct(\PHPStan\Type\Type $mainType) : \PHPStan\Type\Type
    {
        // inspired from https://github.com/phpstan/phpstan-src/blob/94e3443b2d21404a821e05b901dd4b57fcbd4e7f/src/Type/Generic/TemplateTypeHelper.php#L18
        return \PHPStan\Type\TypeTraverser::map($mainType, function (\PHPStan\Type\Type $type, callable $traverse) : Type {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $traverse($type);
            }
            if (!$this->reflectionProvider->hasClass($type->getValue())) {
                return $traverse($type);
            }
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($type->getValue()));
        });
    }
}
