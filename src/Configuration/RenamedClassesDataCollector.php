<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use PHPStan\Type\ObjectType;
use Rector\Core\Contract\DependencyInjection\ResetableInterface;
final class RenamedClassesDataCollector implements ResetableInterface
{
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];
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
        $renamedClassName = $this->oldToNewClasses[$className] ?? null;
        if ($renamedClassName === null) {
            return null;
        }
        return new ObjectType($renamedClassName);
    }
    /**
     * @return string[]
     */
    public function getOldClasses() : array
    {
        return \array_keys($this->oldToNewClasses);
    }
    public function reset() : void
    {
        $this->oldToNewClasses = [];
    }
}
