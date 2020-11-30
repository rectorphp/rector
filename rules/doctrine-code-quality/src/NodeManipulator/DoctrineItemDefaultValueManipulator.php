<?php declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class DoctrineItemDefaultValueManipulator
{
    /**
     * @var bool
     */
    private $hasModifiedAnnotation = false;

    /**
     * @param string|bool|int $defaultValue
     */
    public function remove(AbstractDoctrineTagValueNode $doctrineTagValueNode, string $item, $defaultValue): void
    {
        if (! $this->hasItemWithDefaultValue($doctrineTagValueNode, $item, $defaultValue)) {
            return;
        }

        $this->hasModifiedAnnotation = true;
        $doctrineTagValueNode->removeItem($item);
    }

    public function resetHasModifiedAnnotation(): void
    {
        $this->hasModifiedAnnotation = false;
    }

    public function hasModifiedAnnotation(): bool
    {
        return $this->hasModifiedAnnotation;
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
