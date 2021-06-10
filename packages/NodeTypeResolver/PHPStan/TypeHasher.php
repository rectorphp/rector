<?php

declare(strict_types=1);

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
    public function areTypesEqual(Type $firstType, Type $secondType): bool
    {
        return $this->createTypeHash($firstType) === $this->createTypeHash($secondType);
    }

    private function createTypeHash(Type $type): string
    {
        if ($type instanceof MixedType) {
            return serialize($type) . $type->isExplicitMixed();
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
            return $type::class . $type->getValue();
        }

        if ($type instanceof UnionType) {
            return $this->createUnionTypeHash($type);
        }

        return $type->describe(VerbosityLevel::value());
    }

    private function resolveUniqueTypeWithClassNameHash(TypeWithClassName $typeWithClassName): string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }

        if ($typeWithClassName instanceof AliasedObjectType) {
            return $typeWithClassName->getFullyQualifiedClass();
        }

        return $typeWithClassName->getClassName();
    }

    private function createUnionTypeHash(UnionType $unionType): string
    {
        $sortedTypes = UnionTypeHelper::sortTypes($unionType->getTypes());
        $sortedUnionType = new UnionType($sortedTypes);

        $booleanType = new BooleanType();
        if ($booleanType->isSuperTypeOf($unionType)->yes()) {
            return $booleanType->describe(VerbosityLevel::precise());
        }

        $normalizedUnionType = clone $sortedUnionType;

        // change alias to non-alias
        $normalizedUnionType = TypeTraverser::map(
            $normalizedUnionType,
            function (Type $type, callable $callable): Type {
                if (! $type instanceof AliasedObjectType) {
                    return $callable($type);
                }

                return new FullyQualifiedObjectType($type->getFullyQualifiedClass());
            }
        );

        return $normalizedUnionType->describe(VerbosityLevel::cache());
    }
}
