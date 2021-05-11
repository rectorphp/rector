<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use PHPStan\Type\ObjectType;

final class RenamedClassesDataCollector
{
    /**
     * @var array<string, string>
     */
    private array $oldToNewClasses = [];

    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function addOldToNewClasses(array $oldToNewClasses): void
    {
        $this->oldToNewClasses = array_merge($this->oldToNewClasses, $oldToNewClasses);
    }

    /**
     * @return array<string, string>
     */
    public function getOldToNewClasses(): array
    {
        return $this->oldToNewClasses;
    }

    public function matchClassName(ObjectType $objectType): ?ObjectType
    {
        $className = $objectType->getClassName();

        $renamedClassName = $this->oldToNewClasses[$className] ?? null;
        if ($renamedClassName === null) {
            return null;
        }

        return new ObjectType($renamedClassName);
    }
}
