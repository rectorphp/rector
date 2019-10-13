<?php

declare(strict_types=1);

namespace Rector\Doctrine\Collector;

use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;

final class UuidMigrationDataCollector
{
    /**
     * @var string[][][]
     */
    private $columnPropertiesByClass = [];

    /**
     * @var string[][][][]
     */
    private $relationPropertiesByClass = [];

    public function addClassAndColumnProperty(string $class, string $propertyName): void
    {
        $this->columnPropertiesByClass[$class]['properties'][] = $propertyName;
    }

    public function addClassToManyRelationProperty(
        string $class,
        string $oldPropertyName,
        string $uuidPropertyName,
        DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode
    ): void {
        $kind = $this->resolveKind($doctrineRelationTagValueNode);

        $this->relationPropertiesByClass[$class][$kind][] = [
            'property_name' => $oldPropertyName,
            'uuid_property_name' => $uuidPropertyName,
        ];
    }

    /**
     * @return string[][][]
     */
    public function getColumnPropertiesByClass(): array
    {
        return $this->columnPropertiesByClass;
    }

    /**
     * @return string[][][][]
     */
    public function getRelationPropertiesByClass(): array
    {
        return $this->relationPropertiesByClass;
    }

    private function resolveKind(DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode): string
    {
        return $doctrineRelationTagValueNode instanceof ToManyTagNodeInterface ? 'to_many_relations' : 'to_one_relations';
    }
}
