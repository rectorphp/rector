<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Utils;

use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class TypeUnwrapper
{
    /**
     * E.g. null|ClassType → ClassType
     */
    public function unwrapNullableType(UnionType $unionType): ?Type
    {
        if (count($unionType->getTypes()) !== 2) {
            return null;
        }

        if (! $unionType->isSuperTypeOf(new NullType())->yes()) {
            return null;
        }

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                continue;
            }

            return $unionedType;
        }

        return null;
    }
}
