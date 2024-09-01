<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
final class UnionTypeAnalyzer
{
    public function isNullable(UnionType $unionType) : bool
    {
        return TypeCombinator::containsNull($unionType);
    }
}
