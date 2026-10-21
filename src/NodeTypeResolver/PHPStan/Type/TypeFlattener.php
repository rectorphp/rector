<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
/**
 * Local reimplementation of the deprecated PHPStan TypeUtils::flattenTypes().
 */
final class TypeFlattener
{
    /**
     * @return Type[]
     */
    public static function flatten(Type $type): array
    {
        if ($type instanceof UnionType) {
            $types = [];
            foreach ($type->getTypes() as $innerType) {
                foreach (self::flatten($innerType) as $flattenType) {
                    $types[] = $flattenType;
                }
            }
            return $types;
        }
        $constantArrays = $type->getConstantArrays();
        if ($constantArrays === []) {
            return [$type];
        }
        // each ConstantArrayType with N optional keys expands to 2^N variants via getAllArrays(),
        // and the cartesian product across arrays multiplies those counts - bail out before it explodes
        $estimatedCount = 1;
        foreach ($constantArrays as $constantArray) {
            $optionalCount = count($constantArray->getOptionalKeys());
            $arrayCount = $optionalCount <= 20 ? 1 << $optionalCount : \PHP_INT_MAX;
            if ($arrayCount > 16384 || $estimatedCount > 16384 / max($arrayCount, 1)) {
                return [$type];
            }
            $estimatedCount *= $arrayCount;
        }
        $allArrays = [];
        foreach ($constantArrays as $constantArray) {
            $allArrays[] = $constantArray->getAllArrays();
        }
        $result = [];
        foreach (self::combinations($allArrays) as $combination) {
            $intersected = $combination[0];
            for ($i = 1, $count = count($combination); $i < $count; ++$i) {
                $intersected = TypeCombinator::intersect($intersected, $combination[$i]);
            }
            if ($intersected instanceof NeverType) {
                continue;
            }
            $result[] = $intersected;
        }
        return $result;
    }
    /**
     * @param array<array<Type>> $arrays
     * @return iterable<array<Type>>
     */
    private static function combinations(array $arrays): iterable
    {
        if ($arrays === []) {
            yield [];
            return;
        }
        $last = array_pop($arrays);
        foreach (self::combinations($arrays) as $combination) {
            foreach ($last as $value) {
                $next = $combination;
                $next[] = $value;
                yield $next;
            }
        }
    }
}
