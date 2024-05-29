<?php

declare (strict_types=1);
namespace Rector\Configuration;

use PHPStan\Type\ObjectType;
use Rector\Contract\DependencyInjection\ResetableInterface;
final class RenamedClassesDataCollector implements ResetableInterface
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];
    public function reset() : void
    {
        $this->oldToNewClasses = [];
    }
    /**
     * keep public modifier and use internally on matchClassName() method
     * to keep API as on Configuration level
     */
    public function hasOldClass(string $oldClass) : bool
    {
        return isset($this->oldToNewClasses[$oldClass]);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function addOldToNewClasses(array $oldToNewClasses) : void
    {
        /** @var array<string, string> $oldToNewClasses */
        $oldToNewClasses = \array_merge($this->oldToNewClasses, $oldToNewClasses);
        $this->oldToNewClasses = $oldToNewClasses;
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses() : array
    {
        return $this->oldToNewClasses;
    }
    public function matchClassName(ObjectType $objectType) : ?ObjectType
    {
        $className = $objectType->getClassName();
        if (!$this->hasOldClass($className)) {
            return null;
        }
        return new ObjectType($this->oldToNewClasses[$className]);
    }
    /**
     * @return string[]
     */
    public function getOldClasses() : array
    {
        return \array_keys($this->oldToNewClasses);
    }
}
