<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\BooleanType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
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
