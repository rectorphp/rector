<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
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
        return new StringType();
    }
}
