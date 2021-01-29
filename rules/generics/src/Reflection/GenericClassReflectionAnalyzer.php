<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
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

        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (! $resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return false;
        }

        if ($resolvedPhpDocBlock->getExtendsTags() !== []) {
            return true;
        }

        return $resolvedPhpDocBlock->getImplementsTags() !== [];
    }
}
