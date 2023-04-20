<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
final class AccessoryNonEmptyStringTypeCorrector
{
    /**
     * @return \PHPStan\Type\Type|\PHPStan\Type\IntersectionType
     */
    public function correct(Type $mainType)
    {
        if (!$mainType instanceof IntersectionType) {
            return $mainType;
        }
        if (!$mainType->isNonEmptyString()->yes()) {
            return $mainType;
        }
        $clearIntersectionedTypes = [];
        foreach ($mainType->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof AccessoryNonEmptyStringType) {
                continue;
            }
            if ($intersectionedType instanceof AccessoryNonFalsyStringType) {
                continue;
            }
            $clearIntersectionedTypes[] = $intersectionedType;
        }
        if (\count($clearIntersectionedTypes) === 1) {
            return $clearIntersectionedTypes[0];
        }
        return new IntersectionType($clearIntersectionedTypes);
    }
}
