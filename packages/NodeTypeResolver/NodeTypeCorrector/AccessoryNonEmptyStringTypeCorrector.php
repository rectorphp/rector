<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
final class AccessoryNonEmptyStringTypeCorrector
{
    /**
     * @return \PHPStan\Type\IntersectionType|\PHPStan\Type\Type
     */
    public function correct(\PHPStan\Type\Type $mainType)
    {
        if (!$mainType instanceof \PHPStan\Type\IntersectionType) {
            return $mainType;
        }
        if (!$mainType->isSubTypeOf(new \PHPStan\Type\Accessory\AccessoryNonEmptyStringType())->yes()) {
            return $mainType;
        }
        $clearIntersectionedTypes = [];
        foreach ($mainType->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof \PHPStan\Type\Accessory\AccessoryNonEmptyStringType) {
                continue;
            }
            $clearIntersectionedTypes[] = $intersectionedType;
        }
        if (\count($clearIntersectionedTypes) === 1) {
            return $clearIntersectionedTypes[0];
        }
        return new \PHPStan\Type\IntersectionType($clearIntersectionedTypes);
    }
}
