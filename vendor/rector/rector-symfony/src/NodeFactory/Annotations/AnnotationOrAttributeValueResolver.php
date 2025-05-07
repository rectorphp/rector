<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
final class AnnotationOrAttributeValueResolver
{
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $tagValueNodeOrAttribute
     * @return mixed
     */
    public function resolve($tagValueNodeOrAttribute, string $desiredKey)
    {
        if ($tagValueNodeOrAttribute instanceof DoctrineAnnotationTagValueNode) {
            $templateParameter = $tagValueNodeOrAttribute->getValue($desiredKey);
            if ($templateParameter instanceof ArrayItemNode) {
                $templateParameterValue = $templateParameter->value;
                if ($templateParameterValue instanceof StringNode) {
                    $templateParameterValue = $templateParameterValue->value;
                }
                if (\is_string($templateParameterValue)) {
                    return $templateParameterValue;
                }
            }
            $arrayItemNode = $tagValueNodeOrAttribute->getSilentValue();
            if ($arrayItemNode instanceof ArrayItemNode) {
                $arrayItemNodeValue = $arrayItemNode->value;
                if ($arrayItemNodeValue instanceof StringNode) {
                    $arrayItemNodeValue = $arrayItemNodeValue->value;
                }
                if (\is_string($arrayItemNodeValue)) {
                    return $arrayItemNodeValue;
                }
            }
            return null;
        }
        foreach ($tagValueNodeOrAttribute->args as $attributeArg) {
            if (!$this->isKeyEmptyOrMatch($attributeArg, $desiredKey)) {
                continue;
            }
            if (!$attributeArg->value instanceof String_) {
                continue;
            }
            return $attributeArg->value->value;
        }
        return null;
    }
    private function isKeyEmptyOrMatch(Arg $attributeArg, string $desiredKey) : bool
    {
        if (!$attributeArg->name instanceof Identifier) {
            return \true;
        }
        return $attributeArg->name->toString() === $desiredKey;
    }
}
