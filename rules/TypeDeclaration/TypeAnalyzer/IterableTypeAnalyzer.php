<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class IterableTypeAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isIterableType(\PHPStan\Type\Type $type) : bool
    {
        if ($this->isUnionOfIterableTypes($type)) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\IterableType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            if (!$this->reflectionProvider->hasClass($type->getClassName())) {
                return \false;
            }
            $genericObjectTypeClassReflection = $this->reflectionProvider->getClass($type->getClassName());
            if ($genericObjectTypeClassReflection->implementsInterface('Traversable')) {
                return \true;
            }
        }
        return \false;
    }
    private function isUnionOfIterableTypes(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            // nullable union is allowed
            if ($unionedType instanceof \PHPStan\Type\NullType) {
                continue;
            }
            if (!$this->isIterableType($unionedType)) {
                return \false;
            }
        }
        return \true;
    }
}
