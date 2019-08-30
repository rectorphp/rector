<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc\AbstractTagValueNode;
use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

abstract class AbstractDoctrineTagValueNode extends AbstractTagValueNode implements DoctrineTagNodeInterface
{
    /**
     * @var string[]|null
     */
    protected $orderedVisibleItems;

    /**
     * @param string[] $contentItems
     */
    protected function printContentItems(array $contentItems): string
    {
        if ($this->orderedVisibleItems !== null) {
            $contentItems = ArrayItemStaticHelper::filterAndSortVisibleItems($contentItems, $this->orderedVisibleItems);
        }

        if ($contentItems === []) {
            return '';
        }

        return '(' . implode(', ', $contentItems) . ')';
    }

    /**
     * @param PhpDocTagValueNode[] $tagValueNodes
     */
    protected function printTagValueNodesSeparatedByComma(array $tagValueNodes, string $prefix = ''): string
    {
        if ($tagValueNodes === []) {
            return '';
        }

        $itemsAsStrings = [];
        foreach ($tagValueNodes as $tagValueNode) {
            $itemsAsStrings[] = $prefix . (string) $tagValueNode;
        }

        return implode(', ', $itemsAsStrings);
    }
}
