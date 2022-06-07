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
    public function isStringyType(Type $type) : bool
    {
        if ($type instanceof StringType) {
            return \true;
        }
        if ($type instanceof AccessoryNumericStringType) {
            return \true;
        }
        if ($type instanceof IntersectionType || $type instanceof UnionType) {
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
