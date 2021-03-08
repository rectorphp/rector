<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class DetailedTypeAnalyzer
{
    /**
     * @var int
     */
    private const MAX_NUMBER_OF_TYPES = 3;

    public function isTooDetailed(Type $type): bool
    {
        if ($type instanceof UnionType) {
            return count($type->getTypes()) > self::MAX_NUMBER_OF_TYPES;
        }

        if ($type instanceof ConstantArrayType) {
            return count($type->getValueTypes()) > self::MAX_NUMBER_OF_TYPES;
        }

        if ($type instanceof GenericObjectType) {
            return $this->isTooDetailedGenericObjectType($type);
        }

        return false;
    }

    private function isTooDetailedGenericObjectType(Type $type): bool
    {
        if (count($type->getTypes()) !== 1) {
            return false;
        }

        $genericType = $type->getTypes()[0];
        return $this->isTooDetailed($genericType);
    }
}
