<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\AttributeGroup;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
final class AttrGroupsFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    public function __construct(\Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
    }
    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return AttributeGroup[]
     */
    public function create(array $doctrineTagAndAnnotationToAttributes) : array
    {
        $attributeGroups = [];
        foreach ($doctrineTagAndAnnotationToAttributes as $doctrineTagAndAnnotationToAttribute) {
            $doctrineAnnotationTagValueNode = $doctrineTagAndAnnotationToAttribute->getDoctrineAnnotationTagValueNode();
            // add attributes
            $attributeGroups[] = $this->phpAttributeGroupFactory->create($doctrineAnnotationTagValueNode, $doctrineTagAndAnnotationToAttribute->getAnnotationToAttribute());
        }
        return $attributeGroups;
    }
}
