<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
final class UnionTypeAnalyzer
{
    public function isNullable(UnionType $unionType, bool $checkTwoTypes = \false) : bool
    {
        $types = $unionType->getTypes();
        if ($checkTwoTypes && \count($types) > 2) {
            return \false;
        }
        foreach ($types as $type) {
            if ($type instanceof NullType) {
                return \true;
            }
        }
        return \false;
    }
}
