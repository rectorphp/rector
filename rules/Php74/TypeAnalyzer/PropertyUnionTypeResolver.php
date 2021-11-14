<?php

declare (strict_types=1);
namespace Rector\Php74\TypeAnalyzer;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class PropertyUnionTypeResolver
{
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name $phpUnionType
     */
    public function resolve($phpUnionType, \PHPStan\Type\Type $possibleUnionType) : \PHPStan\Type\Type
    {
        if (!$phpUnionType instanceof \PhpParser\Node\NullableType) {
            return $possibleUnionType;
        }
        if (!$possibleUnionType instanceof \PHPStan\Type\UnionType) {
            return $possibleUnionType;
        }
        $types = $possibleUnionType->getTypes();
        foreach ($types as $type) {
            if (!$type instanceof \PHPStan\Type\NullType) {
                return $type;
            }
        }
        return $possibleUnionType;
    }
}
