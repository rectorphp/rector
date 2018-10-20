<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class ClassPropertyCollector
{
    /**
     * @var VariableInfo[][]
     */
    private $classProperties = [];

    public function addPropertyForClass(string $class, string $propertyType, string $propertyName): void
    {
        $this->classProperties[$class][] = new VariableInfo($propertyName, [$propertyType]);
    }

    /**
     * @return VariableInfo[]
     */
    public function getPropertiesForClass(string $class): array
    {
        return $this->classProperties[$class] ?? [];
    }
}
