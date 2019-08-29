<?php declare(strict_types=1);

namespace Rector\Doctrine\Collector;

final class EntityWithAddedPropertyCollector
{
    /**
     * @var string[][][]
     */
    private $propertiesByClass = [];

    public function addClassAndProperty(string $class, string $property): void
    {
        $this->propertiesByClass[$class]['properties'][] = $property;
    }

    public function addClassToManyRelationProperty(string $class, string $property): void
    {
        $this->propertiesByClass[$class]['to_many_relations'][] = $property;
    }

    public function addClassToOneRelationProperty(string $class, string $property): void
    {
        $this->propertiesByClass[$class]['to_one_relations'][] = $property;
    }

    /**
     * @return string[][][]
     */
    public function getPropertiesByClass(): array
    {
        return $this->propertiesByClass;
    }
}
