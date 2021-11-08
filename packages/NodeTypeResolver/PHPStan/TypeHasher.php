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
    public function areTypesEqual(\PHPStan\Type\Type $firstType, \PHPStan\Type\Type $secondType) : bool
    {
        return $this->createTypeHash($firstType) === $this->createTypeHash($secondType);
    }
    public function createTypeHash(\PHPStan\Type\Type $type) : string
    {
        if ($type instanceof \PHPStan\Type\MixedType) {
            return $type->describe(\PHPStan\Type\VerbosityLevel::precise()) . $type->isExplicitMixed();
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return $this->createTypeHash($type->getItemType()) . $this->createTypeHash($type->getKeyType()) . '[]';
        }
        if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return $type->describe(\PHPStan\Type\VerbosityLevel::precise());
        }
        if ($type instanceof \PHPStan\Type\TypeWithClassName) {
            return $this->resolveUniqueTypeWithClassNameHash($type);
        }
        if ($type instanceof \PHPStan\Type\ConstantType) {
            return \get_class($type) . $type->getValue();
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            return $this->createUnionTypeHash($type);
        }
        $type = $this->normalizeObjectType($type);
        // normalize iterable
        $type = \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $currentType, callable $traverseCallback) : Type {
            if (!$currentType instanceof \PHPStan\Type\ObjectType) {
                return $traverseCallback($currentType);
            }
            if ($currentType->getClassName() === 'iterable') {
                return new \PHPStan\Type\IterableType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
            }
            return $traverseCallback($currentType);
        });
        return $type->describe(\PHPStan\Type\VerbosityLevel::value());
    }
    private function resolveUniqueTypeWithClassNameHash(\PHPStan\Type\TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    private function createUnionTypeHash(\PHPStan\Type\UnionType $unionType) : string
    {
        $booleanType = new \PHPStan\Type\BooleanType();
        if ($booleanType->isSuperTypeOf($unionType)->yes()) {
            return $booleanType->describe(\PHPStan\Type\VerbosityLevel::precise());
        }
        $normalizedUnionType = clone $unionType;
        // change alias to non-alias
        $normalizedUnionType = \PHPStan\Type\TypeTraverser::map($normalizedUnionType, function (\PHPStan\Type\Type $type, callable $callable) : Type {
            if (!$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && !$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                return $callable($type);
            }
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($type->getFullyQualifiedName());
        });
        return $normalizedUnionType->describe(\PHPStan\Type\VerbosityLevel::precise());
    }
    private function normalizeObjectType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $currentType, callable $traverseCallback) : Type {
            if ($currentType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($currentType->getFullyQualifiedName());
            }
            if ($currentType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($currentType->getFullyQualifiedName());
            }
            if ($currentType instanceof \PHPStan\Type\ObjectType && !$currentType instanceof \PHPStan\Type\Generic\GenericObjectType && !$currentType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && $currentType->getClassName() !== 'Iterator' && $currentType->getClassName() !== 'iterable') {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($currentType->getClassName());
            }
            return $traverseCallback($currentType);
        });
    }
}
