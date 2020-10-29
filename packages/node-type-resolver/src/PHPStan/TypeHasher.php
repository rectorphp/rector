<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class TypeHasher
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    public function __construct(PHPStanStaticTypeMapper $phpStanStaticTypeMapper)
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function createTypeHash(Type $type): string
    {
        if ($type instanceof MixedType) {
            return serialize($type);
        }

        if ($type instanceof ArrayType) {
            return $this->createTypeHash($type->getItemType()) . '[]';
        }

        if ($type instanceof GenericObjectType) {
            return $this->phpStanStaticTypeMapper->mapToDocString($type);
        }

        if ($type instanceof TypeWithClassName) {
            return $this->resolveUniqueTypeWithClassNameHash($type);
        }

        if ($type instanceof ConstantType) {
            if (method_exists($type, 'getValue')) {
                return get_class($type) . $type->getValue();
            }

            throw new ShouldNotHappenException();
        }

        if ($type instanceof UnionType) {
            return $this->createUnionTypeHash($type);
        }

        return $this->phpStanStaticTypeMapper->mapToDocString($type);
    }

    public function areTypesEqual(Type $firstType, Type $secondType): bool
    {
        return $this->createTypeHash($firstType) === $this->createTypeHash($secondType);
    }

    private function resolveUniqueTypeWithClassNameHash(Type $type): string
    {
        if ($type instanceof ShortenedObjectType) {
            return $type->getFullyQualifiedName();
        }

        if ($type instanceof AliasedObjectType) {
            return $type->getFullyQualifiedClass();
        }

        return $type->getClassName();
    }

    private function createUnionTypeHash(UnionType $unionType): string
    {
        $unionedTypesHashes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $unionedTypesHashes[] = $this->createTypeHash($unionedType);
        }

        sort($unionedTypesHashes);
        $unionedTypesHashes = array_unique($unionedTypesHashes);

        return implode('|', $unionedTypesHashes);
    }
}
