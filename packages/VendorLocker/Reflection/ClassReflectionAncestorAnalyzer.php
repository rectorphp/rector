<?php

declare(strict_types=1);

namespace Rector\VendorLocker\Reflection;

use PHPStan\Reflection\ClassReflection;

final class ClassReflectionAncestorAnalyzer
{
    public function hasAncestors(ClassReflection $classReflection): bool
    {
        return $classReflection->getAncestors() !== [$classReflection];
    }
}
