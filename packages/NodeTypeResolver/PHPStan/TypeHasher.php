<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\UnionTypeHelper;
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
    private function createTypeHash(\PHPStan\Type\Type $type) : string
    {
        if ($type instanceof \PHPStan\Type\MixedType) {
            return \serialize($type) . $type->isExplicitMixed();
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
        return $type->describe(\PHPStan\Type\VerbosityLevel::value());
    }
    private function resolveUniqueTypeWithClassNameHash(\PHPStan\Type\TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            return $typeWithClassName->getFullyQualifiedClass();
        }
        return $typeWithClassName->getClassName();
    }
    private function createUnionTypeHash(\PHPStan\Type\UnionType $unionType) : string
    {
        $sortedTypes = \PHPStan\Type\UnionTypeHelper::sortTypes($unionType->getTypes());
        $sortedUnionType = new \PHPStan\Type\UnionType($sortedTypes);
        $booleanType = new \PHPStan\Type\BooleanType();
        if ($booleanType->isSuperTypeOf($unionType)->yes()) {
            return $booleanType->describe(\PHPStan\Type\VerbosityLevel::precise());
        }
        $normalizedUnionType = clone $sortedUnionType;
        // change alias to non-alias
        $normalizedUnionType = \PHPStan\Type\TypeTraverser::map($normalizedUnionType, function (\PHPStan\Type\Type $type, callable $callable) : Type {
            if (!$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
                return $callable($type);
            }
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($type->getFullyQualifiedClass());
        });
        return $normalizedUnionType->describe(\PHPStan\Type\VerbosityLevel::cache());
    }
}
