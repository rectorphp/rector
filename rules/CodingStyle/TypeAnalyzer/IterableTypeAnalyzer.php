<?php

declare (strict_types=1);
namespace Rector\CodingStyle\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class IterableTypeAnalyzer
{
    public function detect(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\IterableType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (!$this->detect($unionedType)) {
                    return \false;
                }
            }
            return \true;
        }
        return \false;
    }
}
