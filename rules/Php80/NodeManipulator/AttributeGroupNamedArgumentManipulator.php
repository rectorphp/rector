<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
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
    private function processReplaceAttr(\PhpParser\Node\Attribute $attribute, string $attrName) : void
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
        if (!$currentArg->value instanceof \PhpParser\Node\Scalar\String_) {
            return;
        }
        $currentArg->name = new \PhpParser\Node\Identifier('type');
    }
}
