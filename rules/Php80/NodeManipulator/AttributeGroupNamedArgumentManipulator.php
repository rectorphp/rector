<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\AttributeGroup;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator;
use Rector\Php80\Contract\AttributeDecoratorInterface;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @var AttributeDecoratorInterface[]
     */
    private $attributeDecorators = [];
    public function __construct(SensioParamConverterAttributeDecorator $sensioParamConverterAttributeDecorator)
    {
        $this->attributeDecorators[] = $sensioParamConverterAttributeDecorator;
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
