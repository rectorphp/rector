<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;

final class AccessoryNonEmptyStringTypeCorrector
{
    /**
     * @var array<class-string<Type>>
     */
    private const INTERSECTION_DISALLOWED_TYPES = [
        AccessoryNonEmptyStringType::class,
        ObjectWithoutClassType::class,
    ];

    public function correct(Type $mainType): Type | IntersectionType
    {
        if (! $mainType instanceof IntersectionType) {
            return $mainType;
        }

        $clearIntersectionedTypes = [];
        foreach ($mainType->getTypes() as $intersectionedType) {
            if (in_array($intersectionedType::class, self::INTERSECTION_DISALLOWED_TYPES, true)) {
                continue;
            }

            $clearIntersectionedTypes[] = $intersectionedType;
        }

        if (count($clearIntersectionedTypes) === 1) {
            return $clearIntersectionedTypes[0];
        }

        $countIntersectionTypes = count($mainType->getTypes());
        $countClearIntersectionedTypes = count($clearIntersectionedTypes);
        if ($countIntersectionTypes === $countClearIntersectionedTypes) {
            return $mainType;
        }

        return new IntersectionType($clearIntersectionedTypes);
    }
}
