<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection;

use PHPStan\Reflection\ClassReflection;

final class ClassReflectionTypesResolver
{
    /**
     * @return string[]
     */
    public function resolve(ClassReflection $classReflection): array
    {
        $types = [];

        if (! $classReflection->isAnonymous()) {
            $types[] = $classReflection->getName();
        }

        // parent classes
        $types = array_merge($types, $classReflection->getParentClassesNames());

        // interfaces
        foreach ($classReflection->getInterfaces() as $classReflection) {
            $types[] = $classReflection->getName();
        }

        // traits
        foreach ($classReflection->getTraits() as $classReflection) {
            $types[] = $classReflection->getName();
        }

        return $types;
    }
}
