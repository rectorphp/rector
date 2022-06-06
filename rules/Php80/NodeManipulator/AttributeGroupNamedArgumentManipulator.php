<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node\Attribute;
use RectorPrefix20220606\PhpParser\Node\AttributeGroup;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @param AttributeGroup[] $attributeGroups
     * @return AttributeGroup[]
     */
    public function processSpecialClassTypes(array $attributeGroups) : array
    {
        foreach ($attributeGroups as $attributeGroup) {
            $attrs = $attributeGroup->attrs;
            foreach ($attrs as $attr) {
                $attrName = \ltrim($attr->name->toString(), '\\');
                $this->processReplaceAttr($attr, $attrName);
            }
        }
        return $attributeGroups;
    }
    /**
     * Special case for JMS Access type, where string is replaced by specific value
     */
    private function processReplaceAttr(Attribute $attribute, string $attrName) : void
    {
        if (!\in_array($attrName, ['JMS\\Serializer\\Annotation\\AccessType', 'JMS\\AccessType'], \true)) {
            return;
        }
        $args = $attribute->args;
        if (\count($args) !== 1) {
            return;
        }
        $currentArg = $args[0];
        if ($currentArg->name !== null) {
            return;
        }
        if (!$currentArg->value instanceof String_) {
            return;
        }
        $currentArg->name = new Identifier('type');
    }
}
