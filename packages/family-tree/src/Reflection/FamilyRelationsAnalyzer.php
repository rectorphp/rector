<?php

declare(strict_types=1);

namespace Rector\FamilyTree\Reflection;

final class FamilyRelationsAnalyzer
{
    /**
     * @return class-string[]
     */
    public function getChildrenOfClass(string $parentClass): array
    {
        $childrenClasses = [];
        foreach (get_declared_classes() as $get_declared_class) {
            if ($get_declared_class === $parentClass) {
                continue;
            }

            if (! is_a($get_declared_class, $parentClass, true)) {
                continue;
            }

            $childrenClasses[] = $get_declared_class;
        }

        return $childrenClasses;
    }

    public function isParentClass(string $class): bool
    {
        foreach (get_declared_classes() as $get_declared_class) {
            if ($get_declared_class === $class) {
                continue;
            }

            if (! is_a($get_declared_class, $class, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
