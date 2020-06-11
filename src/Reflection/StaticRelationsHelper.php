<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

final class StaticRelationsHelper
{
    /**
     * @return string[]
     */
    public static function getChildrenOfClass(string $parentClass): array
    {
        $childrenClasses = [];
        foreach (get_declared_classes() as $declaredClass) {
            if ($declaredClass === $parentClass) {
                continue;
            }

            if (! is_a($declaredClass, $parentClass, true)) {
                continue;
            }

            $childrenClasses[] = $declaredClass;
        }

        return $childrenClasses;
    }
}
