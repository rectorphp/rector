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
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\TypeDeclaration\ValueObject\NestedArrayType;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\TypeDeclaration\TypeNormalizerTest
 */
final class TypeNormalizer
{
    /**
     * @var NestedArrayType[]
     */
    private array $collectedNestedArrayTypes = [];

    public function __construct(
        private TypeFactory $typeFactory,
        private UnionTypeFactory $unionTypeFactory,
//        private PhpDocNodeTraverser $phpDocNodeTraverser,
        private PrivatesAccessor $privatesAccessor
    ) {
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
                    $nonConstantValueTypes[$stringType::class] = $stringType;
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
            $this->collectedNestedArrayTypes[] = new NestedArrayType(
                $type->getItemType(),
                $arrayNesting,
                $type->getKeyType()
            );
        }

        return $this->createUnionedTypesFromArrayTypes($this->collectedNestedArrayTypes);
    }

    /**
     * From "string[]|mixed[]" based on empty array to to "string[]"
     */
    public function normalizeArrayTypeAndArrayNever(Type $type): Type
    {
        return TypeTraverser::map($type, function (Type $traversedType, callable $traverserCallable): Type {
            if ($traversedType instanceof ConstantArrayType && $traversedType->getKeyType() instanceof NeverType && $traversedType->getItemType() instanceof NeverType) {
                // not sure why, but with direct new node everything gets nulled to MixedType
                $this->privatesAccessor->setPrivateProperty($traversedType, 'keyType', new MixedType());
                $this->privatesAccessor->setPrivateProperty($traversedType, 'itemType', new MixedType());

                return $traversedType;
            }

            if ($traversedType instanceof UnionType) {
                $collectedTypes = [];

                foreach ($traversedType->getTypes() as $unionedType) {
                    // basically an empty array - not useful at all
                    if ($this->isArrayNeverType($unionedType)) {
                        continue;
                    }

                    $collectedTypes[] = $unionedType;
                }

                // re-create new union types
                if (count($traversedType->getTypes()) !== count($collectedTypes)) {
                    return $this->typeFactory->createMixedPassedOrUnionType($collectedTypes);
                }
            }

            if ($traversedType instanceof NeverType) {
                return new MixedType();
            }

            return $traverserCallable($traversedType, $traverserCallable);
        });
    }

    /**
     * @param array<string|int, Type> $nonConstantValueTypes
     */
    private function createArrayTypeFromNonConstantValueTypes(array $nonConstantValueTypes): ArrayType
    {
        $nonConstantValueTypes = array_values($nonConstantValueTypes);
        if (count($nonConstantValueTypes) > 1) {
            $nonConstantValueType = $this->unionTypeFactory->createUnionObjectType($nonConstantValueTypes);
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
                $this->collectedNestedArrayTypes[] = new NestedArrayType($unionedType, $arrayNesting);
            }
        }
    }

    /**
     * @param NestedArrayType[] $collectedNestedArrayTypes
     */
    private function createUnionedTypesFromArrayTypes(array $collectedNestedArrayTypes): UnionType | ArrayType
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
            return $this->unionTypeFactory->createUnionObjectType($unionedTypes);
        }

        return $unionedTypes[0];
    }

    private function isArrayNeverType(Type $type): bool
    {
        if (! $type instanceof ArrayType) {
            return false;
        }

        return $type->getKeyType() instanceof NeverType && $type->getItemType() instanceof NeverType;
    }
}
