<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\PHPStan\TypeHasher;
use Rector\PHPStan\TypeFactoryStaticHelper;
use Rector\TypeDeclaration\ValueObject\NestedArrayTypeValueObject;

/**
 * @see \Rector\TypeDeclaration\Tests\TypeNormalizerTest
 */
final class TypeNormalizer
{
    /**
     * @var NestedArrayTypeValueObject[]
     */
    private $collectedNestedArrayTypes = [];

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var TypeHasher
     */
    private $typeHasher;

    public function __construct(TypeFactory $typeFactory, TypeHasher $typeHasher)
    {
        $this->typeFactory = $typeFactory;
        $this->typeHasher = $typeHasher;
    }

    public function convertConstantArrayTypeToArrayType(ConstantArrayType $constantArrayType): ?ArrayType
    {
        $nonConstantValueTypes = [];

        if ($constantArrayType->getItemType() instanceof UnionType) {
            /** @var UnionType $unionType */
            $unionType = $constantArrayType->getItemType();
            foreach ($unionType->getTypes() as $unionedType) {
                if ($unionedType instanceof ConstantStringType) {
                    $stringType = new StringType();
                    $nonConstantValueTypes[get_class($stringType)] = $stringType;
                } elseif ($unionedType instanceof ObjectType) {
                    $nonConstantValueTypes[] = $unionedType;
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }

        return $this->createArrayTypeFromNonConstantValueTypes($nonConstantValueTypes);
    }

    /**
     * Turn nested array union types to unique ones:
     * e.g. int[]|string[][]|bool[][]|string[][]
     * ↓
     * int[]|string[][]|bool[][]
     */
    public function normalizeArrayOfUnionToUnionArray(Type $type, int $arrayNesting = 1): Type
    {
        if (! $type instanceof ArrayType) {
            return $type;
        }

        // first collection of types
        if ($arrayNesting === 1) {
            $this->collectedNestedArrayTypes = [];
        }

        if ($type->getItemType() instanceof ArrayType) {
            ++$arrayNesting;
            $this->normalizeArrayOfUnionToUnionArray($type->getItemType(), $arrayNesting);
        } elseif ($type->getItemType() instanceof UnionType) {
            $this->collectNestedArrayTypeFromUnionType($type->getItemType(), $arrayNesting);
        } else {
            $this->collectedNestedArrayTypes[] = new NestedArrayTypeValueObject(
                $type->getItemType(),
                $arrayNesting,
                $type->getKeyType()
            );
        }

        return $this->createUnionedTypesFromArrayTypes($this->collectedNestedArrayTypes);
    }

    public function uniqueateConstantArrayType(Type $type): Type
    {
        if (! $type instanceof ConstantArrayType) {
            return $type;
        }

        // nothing to normalize
        if ($type->getValueTypes() === []) {
            return $type;
        }

        $uniqueTypes = [];
        $removedKeys = [];
        foreach ($type->getValueTypes() as $key => $valueType) {
            $typeHash = $this->typeHasher->createTypeHash($valueType);

            $valueType = $this->uniqueateConstantArrayType($valueType);
            $valueType = $this->normalizeArrayOfUnionToUnionArray($valueType);

            if (! isset($uniqueTypes[$typeHash])) {
                $uniqueTypes[$typeHash] = $valueType;
            } else {
                $removedKeys[] = $key;
            }
        }

        // re-index keys
        $uniqueTypes = array_values($uniqueTypes);

        $keyTypes = [];
        foreach ($type->getKeyTypes() as $key => $keyType) {
            if (in_array($key, $removedKeys, true)) {
                // remove it
                continue;
            }

            $keyTypes[$key] = $keyType;
        }

        return new ConstantArrayType($keyTypes, $uniqueTypes);
    }

    /**
     * From "string[]|mixed[]" based on empty array to to "string[]"
     */
    public function normalizeArrayTypeAndArrayNever(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        $nonNeverTypes = [];
        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof ArrayType) {
                return $type;
            }

            if ($unionedType->getItemType() instanceof NeverType) {
                continue;
            }

            $nonNeverTypes[] = $unionedType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($nonNeverTypes);
    }

    private function createArrayTypeFromNonConstantValueTypes(array $nonConstantValueTypes): ArrayType
    {
        $nonConstantValueTypes = array_values($nonConstantValueTypes);
        if (count($nonConstantValueTypes) > 1) {
            $nonConstantValueType = TypeFactoryStaticHelper::createUnionObjectType($nonConstantValueTypes);
        } else {
            $nonConstantValueType = $nonConstantValueTypes[0];
        }

        return new ArrayType(new MixedType(), $nonConstantValueType);
    }

    private function collectNestedArrayTypeFromUnionType(UnionType $unionType, int $arrayNesting): void
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
                ++$arrayNesting;
                $this->normalizeArrayOfUnionToUnionArray($unionedType, $arrayNesting);
            } else {
                $this->collectedNestedArrayTypes[] = new NestedArrayTypeValueObject($unionedType, $arrayNesting);
            }
        }
    }

    /**
     * @param NestedArrayTypeValueObject[] $collectedNestedArrayTypes
     */
    private function createUnionedTypesFromArrayTypes(array $collectedNestedArrayTypes): Type
    {
        $unionedTypes = [];
        foreach ($collectedNestedArrayTypes as $collectedNestedArrayType) {
            $arrayType = $collectedNestedArrayType->getType();
            for ($i = 0; $i < $collectedNestedArrayType->getArrayNestingLevel(); ++$i) {
                $arrayType = new ArrayType($collectedNestedArrayType->getKeyType(), $arrayType);
            }

            /** @var ArrayType $arrayType */
            $unionedTypes[] = $arrayType;
        }

        if (count($unionedTypes) > 1) {
            return TypeFactoryStaticHelper::createUnionObjectType($unionedTypes);
        }

        return $unionedTypes[0];
    }
}
