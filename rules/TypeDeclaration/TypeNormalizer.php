<?php

declare (strict_types=1);
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
use Rector\TypeDeclaration\ValueObject\NestedArrayType;
use RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
/**
 * @see \Rector\Tests\TypeDeclaration\TypeNormalizerTest
 */
final class TypeNormalizer
{
    /**
     * @var NestedArrayType[]
     */
    private $collectedNestedArrayTypes = [];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor)
    {
        $this->typeFactory = $typeFactory;
        $this->privatesAccessor = $privatesAccessor;
    }
    public function convertConstantArrayTypeToArrayType(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : ?\PHPStan\Type\ArrayType
    {
        $nonConstantValueTypes = [];
        if ($constantArrayType->getItemType() instanceof \PHPStan\Type\UnionType) {
            /** @var UnionType $unionType */
            $unionType = $constantArrayType->getItemType();
            foreach ($unionType->getTypes() as $unionedType) {
                if ($unionedType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                    $stringType = new \PHPStan\Type\StringType();
                    $nonConstantValueTypes[\get_class($stringType)] = $stringType;
                } elseif ($unionedType instanceof \PHPStan\Type\ObjectType) {
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
    public function normalizeArrayOfUnionToUnionArray(\PHPStan\Type\Type $type, int $arrayNesting = 1) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\ArrayType) {
            return $type;
        }
        // first collection of types
        if ($arrayNesting === 1) {
            $this->collectedNestedArrayTypes = [];
        }
        if ($type->getItemType() instanceof \PHPStan\Type\ArrayType) {
            ++$arrayNesting;
            $this->normalizeArrayOfUnionToUnionArray($type->getItemType(), $arrayNesting);
        } elseif ($type->getItemType() instanceof \PHPStan\Type\UnionType) {
            $this->collectNestedArrayTypeFromUnionType($type->getItemType(), $arrayNesting);
        } else {
            $this->collectedNestedArrayTypes[] = new \Rector\TypeDeclaration\ValueObject\NestedArrayType($type->getItemType(), $arrayNesting, $type->getKeyType());
        }
        return $this->createUnionedTypesFromArrayTypes($this->collectedNestedArrayTypes);
    }
    /**
     * From "string[]|mixed[]" based on empty array to to "string[]"
     */
    public function normalizeArrayTypeAndArrayNever(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        return \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $traversedType, callable $traverserCallable) : Type {
            if ($this->isConstantArrayNever($traversedType)) {
                // not sure why, but with direct new node everything gets nulled to MixedType
                $this->privatesAccessor->setPrivateProperty($traversedType, 'keyType', new \PHPStan\Type\MixedType());
                $this->privatesAccessor->setPrivateProperty($traversedType, 'itemType', new \PHPStan\Type\MixedType());
                return $traversedType;
            }
            if ($traversedType instanceof \PHPStan\Type\UnionType) {
                $traversedTypeTypes = $traversedType->getTypes();
                $countTraversedTypes = \count($traversedTypeTypes);
                if ($this->isUnionMixedArrayNeverType($countTraversedTypes, $traversedTypeTypes)) {
                    return new \PHPStan\Type\MixedType();
                }
                $collectedTypes = $this->getCollectedTypes($traversedTypeTypes);
                $countCollectedTypes = \count($collectedTypes);
                // re-create new union types
                if ($countTraversedTypes !== $countCollectedTypes && $countTraversedTypes > 2) {
                    return $this->typeFactory->createMixedPassedOrUnionType($collectedTypes);
                }
            }
            if ($traversedType instanceof \PHPStan\Type\NeverType) {
                return new \PHPStan\Type\MixedType();
            }
            return $traverserCallable($traversedType, $traverserCallable);
        });
    }
    private function isConstantArrayNever(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \PHPStan\Type\Constant\ConstantArrayType && $type->getKeyType() instanceof \PHPStan\Type\NeverType && $type->getItemType() instanceof \PHPStan\Type\NeverType;
    }
    /**
     * @param Type[] $traversedTypeTypes
     * @return Type[]
     */
    private function getCollectedTypes(array $traversedTypeTypes) : array
    {
        $collectedTypes = [];
        foreach ($traversedTypeTypes as $traversedTypeType) {
            // basically an empty array - not useful at all
            if ($this->isArrayNeverType($traversedTypeType)) {
                continue;
            }
            $collectedTypes[] = $traversedTypeType;
        }
        return $collectedTypes;
    }
    /**
     * @param Type[] $traversedTypeTypes
     */
    private function isUnionMixedArrayNeverType(int $countTraversedTypes, array $traversedTypeTypes) : bool
    {
        return $countTraversedTypes === 2 && ($this->isArrayNeverType($traversedTypeTypes[0]) || $this->isArrayNeverType($traversedTypeTypes[1]));
    }
    /**
     * @param array<string|int, Type> $nonConstantValueTypes
     */
    private function createArrayTypeFromNonConstantValueTypes(array $nonConstantValueTypes) : \PHPStan\Type\ArrayType
    {
        $nonConstantValueTypes = \array_values($nonConstantValueTypes);
        if (\count($nonConstantValueTypes) > 1) {
            $nonConstantValueType = new \PHPStan\Type\UnionType($nonConstantValueTypes);
        } else {
            $nonConstantValueType = $nonConstantValueTypes[0];
        }
        return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), $nonConstantValueType);
    }
    private function collectNestedArrayTypeFromUnionType(\PHPStan\Type\UnionType $unionType, int $arrayNesting) : void
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\ArrayType) {
                ++$arrayNesting;
                $this->normalizeArrayOfUnionToUnionArray($unionedType, $arrayNesting);
            } else {
                $this->collectedNestedArrayTypes[] = new \Rector\TypeDeclaration\ValueObject\NestedArrayType($unionedType, $arrayNesting);
            }
        }
    }
    /**
     * @param NestedArrayType[] $collectedNestedArrayTypes
     * @return \PHPStan\Type\ArrayType|\PHPStan\Type\UnionType
     */
    private function createUnionedTypesFromArrayTypes(array $collectedNestedArrayTypes)
    {
        $unionedTypes = [];
        foreach ($collectedNestedArrayTypes as $collectedNestedArrayType) {
            $arrayType = $collectedNestedArrayType->getType();
            for ($i = 0; $i < $collectedNestedArrayType->getArrayNestingLevel(); ++$i) {
                $arrayType = new \PHPStan\Type\ArrayType($collectedNestedArrayType->getKeyType(), $arrayType);
            }
            /** @var ArrayType $arrayType */
            $unionedTypes[] = $arrayType;
        }
        if (\count($unionedTypes) > 1) {
            return new \PHPStan\Type\UnionType($unionedTypes);
        }
        return $unionedTypes[0];
    }
    private function isArrayNeverType(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        return $type->getKeyType() instanceof \PHPStan\Type\NeverType && $type->getItemType() instanceof \PHPStan\Type\NeverType;
    }
}
