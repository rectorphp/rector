<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
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
            return $this->createTypeHash($type->getItemType()) . $this->createTypeHash($type->getKeyType()) . '[]';
        }
        if ($type instanceof GenericObjectType) {
            return $type->describe(VerbosityLevel::precise());
        }
        if ($type instanceof TypeWithClassName) {
            return $this->resolveUniqueTypeWithClassNameHash($type);
        }
        if ($type instanceof ConstantType) {
            return \get_class($type) . $type->getValue();
        }
        if ($type instanceof UnionType) {
            return $this->createUnionTypeHash($type);
        }
        $type = $this->normalizeObjectType($type);
        // normalize iterable
        $type = TypeTraverser::map($type, static function (Type $currentType, callable $traverseCallback) : Type {
            if (!$currentType instanceof ObjectType) {
                return $traverseCallback($currentType);
            }
            if ($currentType->getClassName() === 'iterable') {
                return new IterableType(new MixedType(), new MixedType());
            }
            return $traverseCallback($currentType);
        });
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
    private function createUnionTypeHash(UnionType $unionType) : string
    {
        $booleanType = new BooleanType();
        if ($booleanType->isSuperTypeOf($unionType)->yes()) {
            return $booleanType->describe(VerbosityLevel::precise());
        }
        $normalizedUnionType = clone $unionType;
        // change alias to non-alias
        $normalizedUnionType = TypeTraverser::map($normalizedUnionType, static function (Type $type, callable $callable) : Type {
            if (!$type instanceof AliasedObjectType && !$type instanceof ShortenedObjectType) {
                return $callable($type);
            }
            return new FullyQualifiedObjectType($type->getFullyQualifiedName());
        });
        return $normalizedUnionType->describe(VerbosityLevel::precise());
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
            if ($currentType instanceof ObjectType && !$currentType instanceof GenericObjectType && !$currentType instanceof AliasedObjectType && $currentType->getClassName() !== 'Iterator' && $currentType->getClassName() !== 'iterable') {
                return new FullyQualifiedObjectType($currentType->getClassName());
            }
            return $traverseCallback($currentType);
        });
    }
}
