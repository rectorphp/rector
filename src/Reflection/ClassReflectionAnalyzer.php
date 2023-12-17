<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PHPStan\Reflection\ClassReflection;
use ReflectionEnum;
final class ClassReflectionAnalyzer
{
    public function resolveParentClassName(ClassReflection $classReflection) : ?string
    {
        $nativeReflection = $classReflection->getNativeReflection();
        if ($nativeReflection instanceof ReflectionEnum) {
            return null;
        }
        return $nativeReflection->getParentClassName();
    }
}
