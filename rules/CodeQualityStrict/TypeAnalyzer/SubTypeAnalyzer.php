<?php

declare (strict_types=1);
namespace Rector\CodeQualityStrict\TypeAnalyzer;

use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
final class SubTypeAnalyzer
{
    public function isObjectSubType(\PHPStan\Type\Type $checkedType, \PHPStan\Type\Type $mainType) : bool
    {
        if (!$checkedType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        if (!$mainType instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        // parent type to all objects
        if ($mainType->getClassName() === 'stdClass') {
            return \true;
        }
        return $mainType->isSuperTypeOf($checkedType)->yes();
    }
}
