<?php

declare(strict_types=1);

namespace Rector\Php74\TypeAnalyzer;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class PropertyUnionTypeResolver
{
    public function resolve(Name|ComplexType $phpUnionType, Type $possibleUnionType): Type
    {
        if (! $phpUnionType instanceof NullableType) {
            return $possibleUnionType;
        }

        if (! $possibleUnionType instanceof UnionType) {
            return $possibleUnionType;
        }

        $types = $possibleUnionType->getTypes();
        foreach ($types as $type) {
            if (! $type instanceof NullType) {
                return $type;
            }
        }

        return $possibleUnionType;
    }
}
