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
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function correct(Type $mainType) : Type
    {
        // inspired from https://github.com/phpstan/phpstan-src/blob/94e3443b2d21404a821e05b901dd4b57fcbd4e7f/src/Type/Generic/TemplateTypeHelper.php#L18
        return TypeTraverser::map($mainType, function (Type $traversedType, callable $traverseCallback) : Type {
            if (!$traversedType instanceof ConstantStringType) {
                return $traverseCallback($traversedType);
            }
            $value = $traversedType->getValue();
            if (!$this->reflectionProvider->hasClass($value)) {
                return $traverseCallback($traversedType);
            }
            $classReflection = $this->reflectionProvider->getClass($value);
            if ($classReflection->getName() !== $value) {
                return $traverseCallback($traversedType);
            }
            return new GenericClassStringType(new ObjectType($value));
        });
    }
}
