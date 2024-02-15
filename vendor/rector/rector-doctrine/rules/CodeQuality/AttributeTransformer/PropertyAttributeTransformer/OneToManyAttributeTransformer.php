<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\Helper\NodeValueNormalizer;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class OneToManyAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $oneToManyMapping = $entityMapping->matchOneToManyPropertyMapping($property);
        if (!\is_array($oneToManyMapping)) {
            return;
        }
        // handled by OrderBy mapping rule as standalone entity class
        unset($oneToManyMapping[EntityMappingKey::ORDER_BY]);
        $args = $this->nodeFactory->createArgs($oneToManyMapping);
        NodeValueNormalizer::ensureKeyIsClassConstFetch($args, 'targetEntity');
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
    }
    public function getClassName() : string
    {
        return MappingClass::ONE_TO_MANY;
    }
}
