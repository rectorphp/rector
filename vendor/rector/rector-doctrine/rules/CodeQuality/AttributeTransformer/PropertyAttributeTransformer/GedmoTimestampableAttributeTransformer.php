<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class GedmoTimestampableAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $fieldPropertyMapping = $entityMapping->matchFieldPropertyMapping($property);
        $timestampableMapping = $fieldPropertyMapping['gedmo']['timestampable'] ?? null;
        if (!\is_array($timestampableMapping)) {
            return;
        }
        $args = $this->nodeFactory->createArgs($timestampableMapping);
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
    }
    public function getClassName() : string
    {
        return MappingClass::GEDMO_TIMESTAMPABLE;
    }
}
