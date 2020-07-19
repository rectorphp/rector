<?php

declare(strict_types=1);

namespace Rector\FamilyTree\Reflection;

use PhpParser\Node\Expr;

final class FamilyRelationsAnalyzer
{
    /**
     * @var string[]
     */
    private const KNOWN_PARENT_CLASSES = [\PhpParser\Node::class, Expr::class];

    /**
     * @return string[]
     */
    public function getChildrenOfClass(string $parentClass): array
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

    public function isParentClass(string $class): bool
    {
        if (in_array($class, self::KNOWN_PARENT_CLASSES, true)) {
            return true;
        }

        foreach (get_declared_classes() as $declaredClass) {
            if ($declaredClass === $class) {
                continue;
            }

            if (! is_a($declaredClass, $class, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
