<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
final class BoolUnionTypeAnalyzer
{
    public function isBoolUnionType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof BooleanType) {
                return \false;
            }
        }
        return \true;
    }
    public function isNullableBoolUnionType(UnionType $unionType) : bool
    {
        $hasNullable = \false;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                $hasNullable = \true;
                continue;
            }
            if ($unionedType instanceof BooleanType) {
                continue;
            }
            return \false;
        }
        return $hasNullable;
    }
}
