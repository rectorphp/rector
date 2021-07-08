<?php

declare(strict_types=1);

namespace Rector\Php80\NodeFactory;

use PhpParser\Node\AttributeGroup;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;

final class AttrGroupsFactory
{
    public function __construct(
        private PhpAttributeGroupFactory $phpAttributeGroupFactory
    ) {
    }

    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @return AttributeGroup[]
     */
    public function create(array $doctrineTagAndAnnotationToAttributes): array
    {
        $attributeGroups = [];

        foreach ($doctrineTagAndAnnotationToAttributes as $doctrineTagAndAnnotationToAttribute) {
            $doctrineAnnotationTagValueNode = $doctrineTagAndAnnotationToAttribute->getDoctrineAnnotationTagValueNode();

            // add attributes
            $attributeGroups[] = $this->phpAttributeGroupFactory->create(
                $doctrineAnnotationTagValueNode,
                $doctrineTagAndAnnotationToAttribute->getAnnotationToAttribute()
            );
        }

        return $attributeGroups;
    }
}
