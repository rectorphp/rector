<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use PHPStan\Reflection\ClassReflection;

final class GenericClassReflectionAnalyzer
{
    /**
     * Solve isGeneric() ignores extends and similar tags,
     * so it has to be extended with "@extends" and "@implements"
     */
    public function isGeneric(ClassReflection $classReflection): bool
    {
        if ($classReflection->isGeneric()) {
            return true;
        }

        $resolvedPhpDoc = $classReflection->getResolvedPhpDoc();
        if ($resolvedPhpDoc === null) {
            return false;
        }

        if ($resolvedPhpDoc->getExtendsTags() !== []) {
            return true;
        }

        return $resolvedPhpDoc->getImplementsTags() !== [];
    }
}
