<?php

declare (strict_types=1);
namespace Rector\Php73\NodeTypeAnalyzer;

use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class NodeTypeAnalyzer
{
    public function isStringyType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\StringType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\Accessory\AccessoryNumericStringType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\IntersectionType || $type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $innerType) {
                if (!$this->isStringyType($innerType)) {
                    return \false;
                }
            }
            return \true;
        }
        return \false;
    }
}
