<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\PHPStan\TypeFactoryStaticHelper;

final class TypeFactory
{
    /**
     * @param Type[] $types
     */
    public function createMixedPassedOrUnionType(array $types): Type
    {
        $types = $this->unwrapUnionedTypes($types);
        $types = $this->uniquateTypes($types);

        if (count($types) === 0) {
            return new MixedType();
        }

        if (count($types) === 1) {
            return $types[0];
        }

        return TypeFactoryStaticHelper::createUnionObjectType($types);
    }

    /**
     * @param string[] $allTypes
     * @return ObjectType|UnionType
     */
    public function createObjectTypeOrUnionType(array $allTypes): Type
    {
        if (count($allTypes) === 1) {
            return new ObjectType($allTypes[0]);
        }

        if (count($allTypes) > 1) {
            // keep original order, UnionType internally overrides it → impossible to get first type back, e.g. class over interface
            return TypeFactoryStaticHelper::createUnionObjectType($allTypes);
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @param Type[] $types
     * @return Type[]
     */
    public function uniquateTypes(array $types): array
    {
        $uniqueTypes = [];
        foreach ($types as $type) {
            $type = $this->removeValueFromConstantType($type);

            $typeHash = md5(serialize($type));

            $uniqueTypes[$typeHash] = $type;
        }

        // re-index
        return array_values($uniqueTypes);
    }

    /**
     * @param Type[] $types
     * @return Type[]
     */
    private function unwrapUnionedTypes(array $types): array
    {
        // unwrap union types
        $unwrappedTypes = [];
        foreach ($types as $key => $type) {
            if ($type instanceof UnionType) {
                foreach ($type->getTypes() as $subUnionedType) {
                    $unwrappedTypes[] = $subUnionedType;
                }

                unset($types[$key]);
            }
        }

        $types = array_merge($types, $unwrappedTypes);

        // re-index
        return array_values($types);
    }

    private function removeValueFromConstantType(Type $type): Type
    {
        // remove values from constant types
        if ($type instanceof ConstantFloatType) {
            return new FloatType();
        }

        if ($type instanceof ConstantStringType) {
            return new StringType();
        }

        if ($type instanceof ConstantIntegerType) {
            return new IntegerType();
        }

        if ($type instanceof ConstantBooleanType) {
            return new BooleanType();
        }

        return $type;
    }
}
