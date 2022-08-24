<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\AttributeGroup;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\Contract\AttributeDecoratorInterface;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @var AttributeDecoratorInterface[]
     * @readonly
     */
    private $attributeDecorators;
    /**
     * @param AttributeDecoratorInterface[] $attributeDecorators
     */
    public function __construct(array $attributeDecorators)
    {
        $this->attributeDecorators = $attributeDecorators;
    }
    /**
     * @param AttributeGroup[] $attributeGroups
     */
    public function decorate(array $attributeGroups) : void
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attr) {
                $phpAttributeName = $attr->name->getAttribute(AttributeKey::PHP_ATTRIBUTE_NAME);
                foreach ($this->attributeDecorators as $attributeDecorator) {
                    if ($attributeDecorator->getAttributeName() !== $phpAttributeName) {
                        continue;
                    }
                    $attributeDecorator->decorate($attr);
                }
            }
        }
    }
}
