<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class TypeHasher
{
    public function areTypesEqual(Type $firstType, Type $secondType) : bool
    {
        return $this->createTypeHash($firstType) === $this->createTypeHash($secondType);
    }
    public function createTypeHash(Type $type) : string
    {
        if ($type instanceof MixedType) {
            return $type->describe(VerbosityLevel::precise()) . $type->isExplicitMixed();
        }
        if ($type instanceof ArrayType) {
            return $this->createTypeHash($type->getIterableValueType()) . $this->createTypeHash($type->getIterableKeyType()) . '[]';
        }
        if ($type instanceof GenericObjectType) {
            return $type->describe(VerbosityLevel::precise());
        }
        if ($type instanceof TypeWithClassName) {
            return $this->resolveUniqueTypeWithClassNameHash($type);
        }
        if ($type->isConstantValue()->yes()) {
            return \get_class($type);
        }
        $type = $this->normalizeObjectType($type);
        return $type->describe(VerbosityLevel::value());
    }
    private function resolveUniqueTypeWithClassNameHash(TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        if ($typeWithClassName instanceof AliasedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    private function normalizeObjectType(Type $type) : Type
    {
        return TypeTraverser::map($type, static function (Type $currentType, callable $traverseCallback) : Type {
            if ($currentType instanceof ShortenedObjectType) {
                return new FullyQualifiedObjectType($currentType->getFullyQualifiedName());
            }
            if ($currentType instanceof AliasedObjectType) {
                return new FullyQualifiedObjectType($currentType->getFullyQualifiedName());
            }
            if ($currentType instanceof ObjectType && !$currentType instanceof GenericObjectType && $currentType->getClassName() !== 'Iterator' && $currentType->getClassName() !== 'iterable') {
                return new FullyQualifiedObjectType($currentType->getClassName());
            }
            return $traverseCallback($currentType);
        });
    }
}
