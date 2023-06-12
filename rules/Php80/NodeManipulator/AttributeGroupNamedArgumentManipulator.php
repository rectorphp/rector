<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\AttributeGroup;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @readonly
     * @var \Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator
     */
    private $sensioParamConverterAttributeDecorator;
    public function __construct(SensioParamConverterAttributeDecorator $sensioParamConverterAttributeDecorator)
    {
        $this->sensioParamConverterAttributeDecorator = $sensioParamConverterAttributeDecorator;
    }
    /**
     * @param AttributeGroup[] $attributeGroups
     */
    public function decorate(array $attributeGroups) : void
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attr) {
                $phpAttributeName = $attr->name->getAttribute(AttributeKey::PHP_ATTRIBUTE_NAME);
                if ($this->sensioParamConverterAttributeDecorator->getAttributeName() !== $phpAttributeName) {
                    continue;
                }
                $this->sensioParamConverterAttributeDecorator->decorate($attr);
            }
        }
    }
}
