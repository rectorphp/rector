<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class ClassPropertyCollector
{
    /**
     * @var VariableInfo[][]
     */
    private $classProperties = [];

    /**
     * @param string[] $propertyTypes
     */
    public function addPropertyForClass(string $class, array $propertyTypes, string $propertyName): void
    {
        $this->classProperties[$class][] = VariableInfo::createFromNameAndTypes($propertyName, $propertyTypes);
    }

    /**
     * @return VariableInfo[]
     */
    public function getPropertiesForClass(string $class): array
    {
        return $this->classProperties[$class] ?? [];
    }
}
