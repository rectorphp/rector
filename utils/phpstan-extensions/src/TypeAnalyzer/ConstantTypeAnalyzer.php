<?php
declare(strict_types=1);

namespace Rector\PHPStanExtensions\TypeAnalyzer;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class ConstantTypeAnalyzer
{
    public function isConstantClassStringType(Type $type, string $classString): bool
    {
        if ($type instanceof ConstantArrayType) {
            return $this->matchConstantArrayType($type, $classString);
        }

        if ($type instanceof ConstantStringType) {
            dump($type);
            dump($classString);
            die;
        }

        return false;
    }

    private function matchConstantArrayType(ConstantArrayType $constantArrayType, string $classString): bool
    {
        $itemType = $constantArrayType->getItemType();

        if ($itemType instanceof ConstantStringType) {
            return is_a($itemType->getValue(), $classString, true);
        }

        if (! $itemType instanceof UnionType) {
            return false;
        }

        foreach ($itemType->getTypes() as $unionedType) {
            if (! $unionedType instanceof ConstantStringType) {
                return false;
            }

            if (! is_a($unionedType->getValue(), $classString, true)) {
                return false;
            }
        }

        return true;
    }
}
