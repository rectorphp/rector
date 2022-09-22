<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\HasOffsetValueType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
final class HasOffsetTypeCorrector
{
    /**
     * HasOffsetType breaks array mixed type, so we better get rid of it
     */
    public function correct(Type $type) : Type
    {
        if (!$type instanceof IntersectionType) {
            return $type;
        }
        $clearTypes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof HasOffsetType) {
                continue;
            }
            if ($intersectionedType instanceof NonEmptyArrayType) {
                continue;
            }
            if ($intersectionedType instanceof HasOffsetValueType) {
                continue;
            }
            $clearTypes[] = $intersectionedType;
        }
        if ($clearTypes === []) {
            return new MixedType();
        }
        if (\count($clearTypes) === 1) {
            return $clearTypes[0];
        }
        return new IntersectionType($clearTypes);
    }
}
