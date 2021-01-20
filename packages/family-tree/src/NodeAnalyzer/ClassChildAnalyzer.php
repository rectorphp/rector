<?php

declare(strict_types=1);

namespace Rector\FamilyTree\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionMethod;

final class ClassChildAnalyzer
{
    public function hasChildClassConstructor(Class_ $class): bool
    {
        $childClasses = $this->getChildClasses($class);

        foreach ($childClasses as $childClass) {
            if (! class_exists($childClass)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($childClass);
            $constructorReflectionMethod = $reflectionClass->getConstructor();
            if (! $constructorReflectionMethod instanceof ReflectionMethod) {
                continue;
            }

            if ($constructorReflectionMethod->class !== $childClass) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function hasParentClassConstructor(Class_ $class): bool
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        /** @var string[] $classParents */
        $classParents = (array) class_parents($className);

        foreach ($classParents as $classParent) {
            $parentReflectionClass = new ReflectionClass($classParent);
            $constructMethodReflection = $parentReflectionClass->getConstructor();
            if (! $constructMethodReflection instanceof ReflectionMethod) {
                continue;
            }

            if ($constructMethodReflection->class !== $classParent) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return class-string[]
     */
    private function getChildClasses(Class_ $class): array
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return [];
        }

        $childClasses = [];
        foreach (get_declared_classes() as $declaredClass) {
            if (! is_a($declaredClass, $className, true)) {
                continue;
            }

            if ($declaredClass === $className) {
                continue;
            }

            $childClasses[] = $declaredClass;
        }

        return $childClasses;
    }
}
