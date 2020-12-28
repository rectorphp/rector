<?php

declare(strict_types=1);

namespace Rector\Sensio\TypeAnalyzer;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

final class ArrayUnionResponseTypeAnalyzer
{
    public function isArrayUnionResponseType(Type $type, string $className): bool
    {
        if (! $type instanceof UnionType) {
            return false;
        }

        $hasArrayType = false;
        $hasResponseType = false;
        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
                $hasArrayType = true;
                continue;
            }

            if ($this->isTypeOfClassName($unionedType, $className)) {
                $hasResponseType = true;
                continue;
            }

            return false;
        }
        if (! $hasArrayType) {
            return false;
        }
        return $hasResponseType;
    }

    private function isTypeOfClassName(Type $type, string $className): bool
    {
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        return is_a($type->getClassName(), $className, true);
    }
}
