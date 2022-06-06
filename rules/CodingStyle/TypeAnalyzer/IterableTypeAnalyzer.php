<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
final class IterableTypeAnalyzer
{
    public function detect(Type $type) : bool
    {
        if ($type instanceof ArrayType) {
            return \true;
        }
        if ($type instanceof IterableType) {
            return \true;
        }
        if ($type instanceof UnionType) {
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
