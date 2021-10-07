<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class TypeFactory
{
    /**
     * @var \Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory
     */
    private $unionTypeFactory;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory $unionTypeFactory, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->unionTypeFactory = $unionTypeFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @param Type[] $types
     */
    public function createMixedPassedOrUnionTypeAndKeepConstant(array $types) : \PHPStan\Type\Type
    {
        $types = $this->unwrapUnionedTypes($types);
        $types = $this->uniquateTypes($types, \true);
        return $this->createUnionOrSingleType($types);
    }
    /**
     * @param Type[] $types
     */
    public function createMixedPassedOrUnionType(array $types) : \PHPStan\Type\Type
    {
        $types = $this->unwrapUnionedTypes($types);
        $types = $this->uniquateTypes($types);
        return $this->createUnionOrSingleType($types);
    }
    /**
     * @template TType as Type
     * @param array<TType> $types
     * @return array<TType>
     */
    public function uniquateTypes(array $types, bool $keepConstant = \false) : array
    {
        $uniqueTypes = [];
        foreach ($types as $type) {
            if (!$keepConstant) {
                $type = $this->removeValueFromConstantType($type);
            }
            $type = $this->normalizeObjectTypes($type);
            $typeHash = $type->describe(\PHPStan\Type\VerbosityLevel::cache());
            $uniqueTypes[$typeHash] = $type;
        }
        // re-index
        return \array_values($uniqueTypes);
    }
    /**
     * @param Type[] $types
     * @return Type[]
     */
    private function unwrapUnionedTypes(array $types) : array
    {
        // unwrap union types
        $unwrappedTypes = [];
        foreach ($types as $type) {
            $flattenTypes = \PHPStan\Type\TypeUtils::flattenTypes($type);
            foreach ($flattenTypes as $flattenType) {
                if ($flattenType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                    $unwrappedTypes = \array_merge($unwrappedTypes, $this->unwrapConstantArrayTypes($flattenType));
                } else {
                    $unwrappedTypes = $this->resolveNonConstantArrayType($flattenType, $unwrappedTypes);
                }
            }
        }
        return $unwrappedTypes;
    }
    /**
     * @param Type[] $unwrappedTypes
     * @return Type[]
     */
    private function resolveNonConstantArrayType(\PHPStan\Type\Type $type, array $unwrappedTypes) : array
    {
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $type->getClassName() === 'Rector\\Core\\Stubs\\DummyTraitClass') {
            return $unwrappedTypes;
        }
        $unwrappedTypes[] = $type;
        return $unwrappedTypes;
    }
    /**
     * @param Type[] $types
     */
    private function createUnionOrSingleType(array $types) : \PHPStan\Type\Type
    {
        if ($types === []) {
            return new \PHPStan\Type\MixedType();
        }
        if (\count($types) === 1) {
            return $types[0];
        }
        return $this->unionTypeFactory->createUnionObjectType($types);
    }
    private function removeValueFromConstantType(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        // remove values from constant types
        if ($type instanceof \PHPStan\Type\Constant\ConstantFloatType) {
            return new \PHPStan\Type\FloatType();
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return new \PHPStan\Type\StringType();
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            return new \PHPStan\Type\IntegerType();
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return new \PHPStan\Type\BooleanType();
        }
        return $type;
    }
    /**
     * @return Type[]
     */
    private function unwrapConstantArrayTypes(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : array
    {
        $unwrappedTypes = [];
        $flattenKeyTypes = \PHPStan\Type\TypeUtils::flattenTypes($constantArrayType->getKeyType());
        $flattenItemTypes = \PHPStan\Type\TypeUtils::flattenTypes($constantArrayType->getItemType());
        foreach ($flattenItemTypes as $position => $nestedFlattenItemType) {
            /** @var Type|null $nestedFlattenKeyType */
            $nestedFlattenKeyType = $flattenKeyTypes[$position] ?? null;
            if ($nestedFlattenKeyType === null) {
                $nestedFlattenKeyType = new \PHPStan\Type\MixedType();
            }
            $unwrappedTypes[] = new \PHPStan\Type\ArrayType($nestedFlattenKeyType, $nestedFlattenItemType);
        }
        return $unwrappedTypes;
    }
    private function normalizeObjectTypes(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $traversedType, callable $traverseCallback) : Type {
            if ($this->isStatic($traversedType) && $this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::STATIC_RETURN_TYPE)) {
                /** @var ObjectType $traversedType */
                return new \PHPStan\Type\ThisType($traversedType->getClassName());
            }
            if ($this->isStatic($traversedType)) {
                return new \PHPStan\Type\MixedType();
            }
            if ($this->isSelf($traversedType)) {
                /** @var ObjectType $traversedType */
                return new \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType($traversedType->getClassName());
            }
            if ($traversedType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($traversedType->getFullyQualifiedName());
            }
            if ($traversedType instanceof \PHPStan\Type\ObjectType && !$traversedType instanceof \PHPStan\Type\Generic\GenericObjectType && !$traversedType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && $traversedType->getClassName() !== 'Iterator') {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($traversedType->getClassName());
            }
            return $traverseCallback($traversedType);
        });
    }
    private function isStatic(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \PHPStan\Type\ObjectType && $type->getClassName() === 'static';
    }
    private function isSelf(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \PHPStan\Type\ObjectType && $type->getClassName() === 'self';
    }
}
