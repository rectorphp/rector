<?php

declare(strict_types=1);

namespace Rector\FamilyTree\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @deprecated Use ReflectionProvider instead
 */
final class FamilyRelationsAnalyzer
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @return ClassReflection[]
     */
    public function getChildrenOfClass(string $className): array
    {
        $classReflection = $this->reflectionProvider->getClass($className);

        $childrenClassReflections = [];
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($ancestorClassReflection === $classReflection) {
                continue;
            }

            $childrenClassReflections[] = $ancestorClassReflection;
        }

        return $childrenClassReflections;
    }
}
