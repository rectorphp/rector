<?php

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class DoctrineItemDefaultValueManipulator
{
    private $hasModifiedAnnotation = false;

    public function remove(
        AbstractDoctrineTagValueNode $doctrineTagValueNode,
        string $item,
        $defaultValue
    ): void {
        if (! $this->hasItemWithDefaultValue($doctrineTagValueNode, $item, $defaultValue)) {
            return;
        }

        $this->hasModifiedAnnotation = true;
        $doctrineTagValueNode->removeItem($item);
    }

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

    public function setHasModifiedAnnotation(bool $hasModifiedAnnotation): void
    {
        $this->hasModifiedAnnotation = $hasModifiedAnnotation;
    }

    public function hasModifiedAnnotation(): bool
    {
        return $this->hasModifiedAnnotation;
    }
}
