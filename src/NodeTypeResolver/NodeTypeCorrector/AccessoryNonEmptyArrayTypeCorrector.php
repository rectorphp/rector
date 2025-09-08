<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
final class AccessoryNonEmptyArrayTypeCorrector
{
    public function correct(Type $mainType): Type
    {
        if (!$mainType instanceof IntersectionType) {
            return $mainType;
        }
        if (!$mainType->isArray()->yes()) {
            return $mainType;
        }
        foreach ($mainType->getTypes() as $type) {
            if ($type instanceof NonEmptyArrayType) {
                return new ArrayType(new MixedType(), new MixedType());
            }
        }
        return $mainType;
    }
}
