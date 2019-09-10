<?php declare(strict_types=1);

namespace Rector\PSR4\Collector;

use Rector\PSR4\ValueObject\ClassRenameValueObject;

final class RenamedClassesCollector
{
    /**
     * @var ClassRenameValueObject[]
     */
    private $classRenames = [];

    public function addClassRename(string $oldClass, string $newClass): void
    {
        $parentClasses = $this->getParentClasses($oldClass);

        $this->classRenames[$oldClass] = new ClassRenameValueObject($oldClass, $newClass, $parentClasses);
    }

    /**
     * @return string[]
     */
    public function getOldToNewClassesSortedByHighestParentsAsString(): array
    {
        $oldToNewClasses = [];
        foreach ($this->getClassRenamesSortedByHighestParents() as $classRename) {
            $oldToNewClasses[$classRename->getOldClass()] = $classRename->getNewClass();
        }

        return $oldToNewClasses;
    }

    /**
     * @return ClassRenameValueObject[]
     */
    public function getClassRenamesSortedByHighestParents(): array
    {
        $this->filterOutParentsThatAreNotCollected();

        $leftClassPriorityStack = $this->createLeftClassPriorityStack();

        return $this->sortClassRenamedByLeftClassPriorityStack($this->classRenames, $leftClassPriorityStack);
    }

    public function hasClassRenames(): bool
    {
        return $this->classRenames !== [];
    }

    /**
     * Returns all parent classes, implemented interfaces and used traits by specific class
     *
     * @return string[]
     */
    private function getParentClasses(string $className): array
    {
        $parentClasses = class_parents($className) + class_implements($className) + class_uses($className);

        return array_values($parentClasses);
    }

    /**
     * We don't need to sort classes that are not here.
     */
    private function filterOutParentsThatAreNotCollected(): void
    {
        foreach ($this->classRenames as $classRename) {
            foreach ($classRename->getParentClasses() as $parent) {
                if (isset($this->classRenames[$parent])) {
                    continue;
                }

                $classRename->removeParent($parent);
            }
        }
    }

    /**
     * @return string[]
     */
    private function createLeftClassPriorityStack(): array
    {
        $leftClassPriorityStack = [];
        foreach ($this->classRenames as $classRename) {
            foreach ($classRename->getParentClasses() as $parentClass) {
                $leftClassPriorityStack[$parentClass] = $classRename->getOldClass();
            }
        }

        return $leftClassPriorityStack;
    }

    /**
     * @param ClassRenameValueObject[] $classRenames
     * @param string[] $leftClassPriorityStack
     * @return ClassRenameValueObject[]
     */
    private function sortClassRenamedByLeftClassPriorityStack(array $classRenames, array $leftClassPriorityStack): array
    {
        usort($classRenames, function (
            ClassRenameValueObject $firstClassRename,
            ClassRenameValueObject $secondClassRename
        ) use ($leftClassPriorityStack): int {
            if ($secondClassRename->getParentClasses() === []) {
                // no parents, put first
                return 1;
            }

            if (! isset($leftClassPriorityStack[$secondClassRename->getOldClass()])) {
                return -1;
            }

            $lessPriorityClass = $leftClassPriorityStack[$secondClassRename->getOldClass()];
            if ($lessPriorityClass === $firstClassRename->getOldClass()) {
                // put class with higher priority first
                return 1;
            }

            return -1;
        });

        return $classRenames;
    }
}
