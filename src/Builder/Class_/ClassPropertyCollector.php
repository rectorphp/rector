<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class ClassPropertyCollector
{
    /**
     * @var string[][]
     */
    private $classProperties = [];

    public function addPropertyForClass(string $class, string $propertyType, string $propertyName): void
    {
        $this->classProperties[$class] = [
            $propertyType => $propertyName
        ];
    }

    /**
     * @return string[]
     */
    public function getPropertiesforClass(string $class): array
    {
        return $this->classProperties[$class] ?? [];
    }
}
