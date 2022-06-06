<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php73\NodeTypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\Accessory\AccessoryNumericStringType;
use RectorPrefix20220606\PHPStan\Type\IntersectionType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
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
