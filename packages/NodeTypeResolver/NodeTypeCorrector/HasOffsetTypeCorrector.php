<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
final class HasOffsetTypeCorrector
{
    /**
     * HasOffsetType breaks array mixed type, so we better get rid of it
     */
    public function correct(\PHPStan\Type\Type $type) : \PHPStan\Type\Type
    {
        if (!$type instanceof \PHPStan\Type\IntersectionType) {
            return $type;
        }
        $clearTypes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof \PHPStan\Type\Accessory\HasOffsetType) {
                continue;
            }
            if ($intersectionedType instanceof \PHPStan\Type\Accessory\NonEmptyArrayType) {
                continue;
            }
            $clearTypes[] = $intersectionedType;
        }
        if (\count($clearTypes) === 1) {
            return $clearTypes[0];
        }
        return new \PHPStan\Type\IntersectionType($clearTypes);
    }
}
