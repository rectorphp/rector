<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class ColumnAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $propertyMapping = $entityMapping->matchFieldPropertyMapping($property);
        if ($propertyMapping === null) {
            return;
        }
        // handled in another mapper
        unset($propertyMapping['gedmo']);
        $args = [];
        // rename to "name"
        if (isset($propertyMapping[EntityMappingKey::COLUMN])) {
            $column = $propertyMapping[EntityMappingKey::COLUMN];
            $args[] = AttributeFactory::createNamedArg($column, EntityMappingKey::NAME);
            unset($propertyMapping[EntityMappingKey::COLUMN]);
        }
        $args = \array_merge($args, $this->nodeFactory->createArgs($propertyMapping));
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
    }
    public function getClassName() : string
    {
        return MappingClass::COLUMN;
    }
}
