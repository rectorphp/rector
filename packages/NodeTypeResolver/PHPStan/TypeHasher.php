<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
//use Rector\StaticTypeMapper\ValueObject\Type\FalseBooleanType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

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

    public function areTypesEqual(Type $firstType, Type $secondType): bool
    {
        return $this->createTypeHash($firstType) === $this->createTypeHash($secondType);
    }

    private function createTypeHash(Type $type): string
    {
        // remove false bool type
//        if ($type instanceof UnionType) {
//            $hasBooleanType = false;
//            foreach ($type->getTypes() as $type) {
//                if ($type instanceof BooleanType) {
//                    $hasBooleanType = true;
//                }
//            }
//
//            if ($hasBooleanType) {
//                $type = TypeCombinator::remove($type, new FalseBooleanType());
//            }
//        }

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
            return get_class($type) . $type->getValue();
        }

        if ($type instanceof UnionType) {
            return $this->createUnionTypeHash($type);
        }

        dump($type);
        dump($type->describe(VerbosityLevel::typeOnly()));
        dump($type->describe(VerbosityLevel::precise()));
        dump($type->describe(VerbosityLevel::cache()));
        dump($type->describe(VerbosityLevel::value()));

        return $type->describe(VerbosityLevel::value());
//        return $this->phpStanStaticTypeMapper->mapToDocString($type);
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
        dump($unionType);

        $unionedTypesHashes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $unionedTypesHashes[] = $this->createTypeHash($unionedType);
        }

        sort($unionedTypesHashes);
        $unionedTypesHashes = array_unique($unionedTypesHashes);

        return implode('|', $unionedTypesHashes);
    }
}
