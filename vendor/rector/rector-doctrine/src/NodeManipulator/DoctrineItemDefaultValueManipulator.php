<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class DoctrineItemDefaultValueManipulator
{
    /**
     * @param string|bool|int $defaultValue
     */
    public function clearDoctrineAnnotationTagValueNode(PhpDocInfo $phpDocInfo, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, string $item, $defaultValue) : bool
    {
        if (!$this->hasItemWithDefaultValue($doctrineAnnotationTagValueNode, $item, $defaultValue)) {
            return \false;
        }
        $parent = $doctrineAnnotationTagValueNode->getAttribute(PhpDocAttributeKey::PARENT);
        if ($parent instanceof ArrayItemNode) {
            return \false;
        }
        $doctrineAnnotationTagValueNode->removeValue($item);
        $phpDocInfo->markAsChanged();
        return \true;
    }
    /**
     * @param string|bool|int $defaultValue
     */
    private function hasItemWithDefaultValue(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, string $itemKey, $defaultValue) : bool
    {
        $currentValueArrayItemNode = $doctrineAnnotationTagValueNode->getValue($itemKey);
        if (!$currentValueArrayItemNode instanceof ArrayItemNode) {
            return \false;
        }
        $currentValue = $currentValueArrayItemNode->value;
        if ($defaultValue === \false) {
            return $currentValue instanceof ConstExprFalseNode;
        }
        if ($defaultValue === \true) {
            return $currentValue instanceof ConstExprTrueNode;
        }
        if (\is_int($defaultValue) && $currentValue instanceof ConstExprIntegerNode) {
            $currentValue = (int) $currentValue->value;
        }
        return $currentValue === $defaultValue;
    }
}
