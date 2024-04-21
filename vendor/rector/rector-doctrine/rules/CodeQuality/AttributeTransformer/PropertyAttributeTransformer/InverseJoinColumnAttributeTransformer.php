<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class InverseJoinColumnAttributeTransformer implements PropertyAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property) : void
    {
        $joinTableMapping = $entityMapping->matchManyToManyPropertyMapping($property)['joinTable'] ?? null;
        if (!\is_array($joinTableMapping)) {
            return;
        }
        $joinColumns = $joinTableMapping['inverseJoinColumns'] ?? null;
        if (!\is_array($joinColumns)) {
            return;
        }
        foreach ($joinColumns as $columnName => $joinColumn) {
            $property->attrGroups[] = $this->createInverseJoinColumnAttrGroup($columnName, $joinColumn);
        }
    }
    public function getClassName() : string
    {
        return MappingClass::INVERSE_JOIN_COLUMN;
    }
    /**
     * @param int|string $columnName
     * @param mixed $joinColumn
     */
    private function createInverseJoinColumnAttrGroup($columnName, $joinColumn) : AttributeGroup
    {
        $joinColumn = \array_merge(['name' => $columnName], $joinColumn);
        $args = $this->nodeFactory->createArgs($joinColumn);
        return AttributeFactory::createGroup($this->getClassName(), $args);
    }
}
