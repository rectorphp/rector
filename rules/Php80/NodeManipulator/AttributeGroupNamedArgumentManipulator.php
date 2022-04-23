<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @var array<string, array<string, string|class-string<Expr>>>
     */
    private const SPECIAL_CLASS_TYPES = ['JMS\\Serializer\\Annotation\\AccessType' => ['common_aliased' => 'JMS\\AccessType', 'value' => 'PhpParser\\Node\\Scalar\\String_', 'name' => 'type']];
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
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
    private function processReplaceAttr(\PhpParser\Node\Attribute $attribute, string $attrName) : void
    {
        foreach (self::SPECIAL_CLASS_TYPES as $classType => $specialClasssType) {
            if ($attrName !== $classType && $attrName !== $specialClasssType['common_aliased']) {
                continue;
            }
            $args = $attribute->args;
            if (\count($args) !== 1) {
                continue;
            }
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($args, 0)) {
                continue;
            }
            /** @var Arg $currentArg */
            $currentArg = $args[0];
            if ($currentArg->name !== null) {
                continue;
            }
            if (!$currentArg->value instanceof $specialClasssType['value']) {
                continue;
            }
            $currentArg->name = new \PhpParser\Node\Identifier($specialClasssType['name']);
        }
    }
}
