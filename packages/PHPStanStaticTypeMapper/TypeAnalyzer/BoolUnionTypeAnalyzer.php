<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeAnalyzer;

use PHPStan\Type\BooleanType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
final class BoolUnionTypeAnalyzer
{
    public function isBoolUnionType(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (!$unionedType instanceof \PHPStan\Type\BooleanType) {
                return \false;
            }
        }
        return \true;
    }
    public function isNullableBoolUnionType(\PHPStan\Type\UnionType $unionType) : bool
    {
        $hasNullable = \false;
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof \PHPStan\Type\NullType) {
                $hasNullable = \true;
                continue;
            }
            if ($unionedType instanceof \PHPStan\Type\BooleanType) {
                continue;
            }
            return \false;
        }
        return $hasNullable;
    }
}
