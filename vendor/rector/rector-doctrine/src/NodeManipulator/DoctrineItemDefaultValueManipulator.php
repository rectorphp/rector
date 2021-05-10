<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
final class DoctrineItemDefaultValueManipulator
{
    /**
     * @param string|bool|int $defaultValue
     */
    public function remove(PhpDocInfo $phpDocInfo, DoctrineAnnotationTagValueNode $doctrineTagValueNode, string $item, $defaultValue) : void
    {
        if (!$this->hasItemWithDefaultValue($doctrineTagValueNode, $item, $defaultValue)) {
            return;
        }
        $doctrineTagValueNode->removeValue($item);
        $phpDocInfo->markAsChanged();
    }
    /**
     * @param string|bool|int $defaultValue
     */
    private function hasItemWithDefaultValue(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, string $itemKey, $defaultValue) : bool
    {
        $currentValue = $doctrineAnnotationTagValueNode->getValueWithoutQuotes($itemKey);
        if ($currentValue === null) {
            return \false;
        }
        if ($defaultValue === \false) {
            return $currentValue instanceof ConstExprFalseNode;
        }
        if ($defaultValue === \true) {
            return $currentValue instanceof ConstExprTrueNode;
        }
        if (\is_int($defaultValue)) {
            if ($currentValue instanceof ConstExprIntegerNode) {
                $currentValue = (int) $currentValue->value;
            }
        }
        return $currentValue === $defaultValue;
    }
}
