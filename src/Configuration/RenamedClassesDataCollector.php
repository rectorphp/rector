<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use PHPStan\Type\ObjectType;
final class RenamedClassesDataCollector
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];
    public function addOldToNewClass(string $oldClass, string $newClass) : void
    {
        $this->oldToNewClasses[$oldClass] = $newClass;
    }
    public function hasOldClass(string $oldClass) : bool
    {
        return isset($this->oldToNewClasses[$oldClass]);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function addOldToNewClasses(array $oldToNewClasses) : void
    {
        $this->oldToNewClasses = \array_merge($this->oldToNewClasses, $oldToNewClasses);
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses() : array
    {
        return $this->oldToNewClasses;
    }
    public function matchClassName(\PHPStan\Type\ObjectType $objectType) : ?\PHPStan\Type\ObjectType
    {
        $className = $objectType->getClassName();
        $renamedClassName = $this->oldToNewClasses[$className] ?? null;
        if ($renamedClassName === null) {
            return null;
        }
        return new \PHPStan\Type\ObjectType($renamedClassName);
    }
    /**
     * @return string[]
     */
    public function getOldClasses() : array
    {
        return \array_keys($this->oldToNewClasses);
    }
}
