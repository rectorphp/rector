<?php declare(strict_types=1);

namespace Rector\Doctrine\Collector;

final class UuidMigrationDataCollector
{
    /**
     * @var mixed[]
     */
    private $propertiesByClass = [];

    public function addClassAndProperty(string $class, string $property): void
    {
        $this->propertiesByClass[$class]['properties'][] = $property;
    }

    public function addClassToManyRelationProperty(
        string $class,
        string $property,
        string $currentTableName,
        string $uuidTableName
    ): void {
        $this->propertiesByClass[$class]['to_many_relations'][] = [
            'property' => $property,
            'current_table_name' => $currentTableName,
            'uuid_table_name' => $uuidTableName,
        ];
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
