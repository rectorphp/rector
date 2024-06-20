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
final class JoinColumnAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $this->transformMapping($property, $entityMapping->matchManyToManyPropertyMapping($property)['joinTable'] ?? null);
        $this->transformMapping($property, $entityMapping->matchManyToOnePropertyMapping($property));
    }
    public function getClassName() : string
    {
        return MappingClass::JOIN_COLUMN;
    }
    /**
     * @param array<string, array<string, mixed>>|null $mapping
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    private function transformMapping($property, ?array $mapping) : void
    {
        if (!\is_array($mapping)) {
            return;
        }
        $singleJoinColumn = $mapping['joinColumn'] ?? null;
        if (\is_array($singleJoinColumn)) {
            $name = $singleJoinColumn['name'];
            unset($singleJoinColumn['name']);
            $mapping['joinColumns'][$name] = $singleJoinColumn;
        }
        $joinColumns = $mapping['joinColumns'] ?? null;
        if (!\is_array($joinColumns)) {
            return;
        }
        foreach ($joinColumns as $columnName => $joinColumn) {
            $property->attrGroups[] = $this->createJoinColumnAttrGroup($columnName, $joinColumn);
        }
    }
    /**
     * @param int|string $columnName
     * @param mixed $joinColumn
     */
    private function createJoinColumnAttrGroup($columnName, $joinColumn) : AttributeGroup
    {
        $joinColumn = \array_merge(['name' => $columnName], $joinColumn);
        $args = $this->nodeFactory->createArgs($joinColumn);
        return AttributeFactory::createGroup($this->getClassName(), $args);
    }
}
