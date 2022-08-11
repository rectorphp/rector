<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PHPStan\Reflection\ClassReflection;
final class SealedClassAnalyzer
{
    /**
     * This method is perfectly sealed, nothing to downgrade here
     */
    public function isSealedClass(ClassReflection $classReflection) : bool
    {
        if (!$classReflection->isClass()) {
            return \false;
        }
        if (!$classReflection->isFinal()) {
            return \false;
        }
        return \count($classReflection->getAncestors()) === 1;
    }
}
