<?php declare(strict_types=1);

namespace Rector\Doctrine\Collector;

final class EntitiesWithAddedPropertyCollector
{
    /**
     * @var string[]
     */
    private $propertiesByClasses = [];

    public function addClassAndProperty(string $class, string $property): void
    {
        $this->propertiesByClasses[$class][] = $property;
    }

    /**
     * @return string[]
     */
    public function getPropertiesByClasses(): array
    {
        return $this->propertiesByClasses;
    }
}
