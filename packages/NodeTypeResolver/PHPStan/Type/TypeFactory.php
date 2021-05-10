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
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class TypeFactory
{
    /**
     * @var UnionTypeFactory
     */
    private $unionTypeFactory;
    public function __construct(\Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory $unionTypeFactory)
    {
        $this->unionTypeFactory = $unionTypeFactory;
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
     * @param Type[] $types
     * @return Type[]
     */
    public function uniquateTypes(array $types, bool $keepConstant = \false) : array
    {
        $uniqueTypes = [];
        foreach ($types as $type) {
            if (!$keepConstant) {
                $type = $this->removeValueFromConstantType($type);
            }
            if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                $type = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($type->getFullyQualifiedName());
            }
            if ($type instanceof \PHPStan\Type\ObjectType && !$type instanceof \PHPStan\Type\Generic\GenericObjectType && !$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType && $type->getClassName() !== 'Iterator') {
                $type = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($type->getClassName());
            }
            $typeHash = \md5($type->describe(\PHPStan\Type\VerbosityLevel::cache()));
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
                    $unwrappedTypes[] = $flattenType;
                }
            }
        }
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
}
