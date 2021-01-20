<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class DoctrineItemDefaultValueManipulator
{
    /**
     * @param string|bool|int $defaultValue
     */
    public function remove(
        PhpDocInfo $phpDocInfo,
        AbstractDoctrineTagValueNode $doctrineTagValueNode,
        string $item,
        $defaultValue
    ): void {
        if (! $this->hasItemWithDefaultValue($doctrineTagValueNode, $item, $defaultValue)) {
            return;
        }

        $doctrineTagValueNode->removeItem($item);
        $phpDocInfo->markAsChanged();
    }

    /**
     * @param string|bool|int $defaultValue
     */
    private function hasItemWithDefaultValue(
        AbstractDoctrineTagValueNode $doctrineTagValueNode,
        string $item,
        $defaultValue
    ): bool {
        $attributableItems = $doctrineTagValueNode->getAttributableItems();
        if (! isset($attributableItems[$item])) {
            return false;
        }

        return $attributableItems[$item] === $defaultValue;
    }
}
