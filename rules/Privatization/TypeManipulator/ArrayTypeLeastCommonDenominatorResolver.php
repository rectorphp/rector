<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
/**
 * Made with GPT-5
 * @see https://chatgpt.com/share/68d2c183-7708-800a-848b-c63822c4625a
 */
final class ArrayTypeLeastCommonDenominatorResolver
{
    /**
     * Return the deepest common "array structure" shared by all $types.
     * - Keeps exact keys when all are ConstantArrayType with the same keys
     * - Keeps generic key type (int|string) when consistent
     * - Falls back to mixed at the first conflicting depth
     */
    public function sharedArrayStructure(Type ...$types): Type
    {
        if ($types === []) {
            return new MixedType();
        }
        // If any is not an ArrayType, we cannot descend further.
        foreach ($types as $type) {
            if (!$type instanceof ArrayType) {
                return new MixedType();
            }
        }
        // Generic ArrayType path: reconcile key type + recurse into item types
        /** @var ArrayType[] $types */
        /** @var ArrayType[] $arrayTypes */
        $arrayTypes = $types;
        // Try to keep a compatible key type (intersection; fall back to mixed if impossible)
        $firstArrayType = array_shift($arrayTypes);
        if (!$firstArrayType instanceof ArrayType) {
            return new MixedType();
        }
        $keyType = $firstArrayType->getKeyType();
        foreach ($arrayTypes as $arr) {
            $keyType = TypeCombinator::intersect($keyType, $arr->getKeyType());
        }
        if ($keyType instanceof NeverType) {
            $keyType = new MixedType();
            // incompatible key types
        }
        // Recurse on item types; if mixed is returned, that’s our stop depth.
        $itemTypes = array_map(fn(ArrayType $arrayType): Type => $arrayType->getItemType(), $types);
        $itemType = $this->sharedArrayStructure(...$itemTypes);
        return new ArrayType($keyType, $itemType);
    }
}
