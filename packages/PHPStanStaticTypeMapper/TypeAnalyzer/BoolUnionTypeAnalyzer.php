<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\UnionType;
final class BoolUnionTypeAnalyzer
{
    public function isBoolUnionType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType->isBoolean()->yes()) {
                return \false;
            }
        }
        return \true;
    }
}
