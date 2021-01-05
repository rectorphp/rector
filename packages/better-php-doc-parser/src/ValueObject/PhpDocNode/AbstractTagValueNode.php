<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PartPhpDocTagPrinter\Behavior\ArrayPartPhpDocTagPrinterTrait;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

abstract class AbstractTagValueNode implements AttributeAwareInterface, PhpDocTagValueNode
{
    use AttributesTrait;
    use ArrayPartPhpDocTagPrinterTrait;

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * @var TagValueNodeConfiguration
     */
    protected $tagValueNodeConfiguration;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items, ?string $originalContent = null)
    {
        $this->items = $items;
    }

    /**
     * Generic fallback
     */
    public function __toString(): string
    {
        return $this->printItems($this->items);
    }

    /**
     * @return mixed[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param mixed $value
     */
    public function changeItem(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    public function removeItem(string $key): void
    {
        unset($this->items[$key]);
    }

    /**
     * @param mixed[] $contentItems
     * @return mixed[]
     */
    public function filterOutMissingItems(array $contentItems): array
    {
        if ($this->tagValueNodeConfiguration->getOrderedVisibleItems() === null) {
            return $contentItems;
        }

        return ArrayItemStaticHelper::filterAndSortVisibleItems(
            $contentItems,
            $this->tagValueNodeConfiguration->getOrderedVisibleItems()
        );
    }

    /**
     * @param mixed[] $items
     */
    protected function printItems(array $items): string
    {
        $items = $this->completeItemsQuotes($items);
        $items = $this->filterOutMissingItems($items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    /**
     * @param string[] $items
     */
    protected function printContentItems(array $items): string
    {
        $items = $this->filterOutMissingItems($items);

        // remove null values
        $items = array_filter($items);

        if ($items === []) {
            if ($this->shouldPrintEmptyBrackets()) {
                return '()';
            }

            return '';
        }

        // print array value to string
        foreach ($items as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $arrayItemAsString = $this->printArrayItem($value, $key, $this->tagValueNodeConfiguration);
            $arrayItemAsString = $this->correctArraySingleItemPrint($value, $arrayItemAsString);

            /** @var string $key */
            $items[$key] = $arrayItemAsString;
        }

        return sprintf(
            '(%s%s%s)',
            $this->tagValueNodeConfiguration->hasNewlineAfterOpening() ? PHP_EOL : '',
            implode(', ', $items),
            $this->tagValueNodeConfiguration->hasNewlineBeforeClosing() ? PHP_EOL : ''
        );
    }

    /**
     * @param PhpDocTagValueNode[] $tagValueNodes
     */
    protected function printNestedTag(array $tagValueNodes, bool $haveFinalComma): string
    {
        $tagValueNodesAsString = $this->printTagValueNodesSeparatedByComma($tagValueNodes);

        return sprintf('{%s%s%s%s}', $tagValueNodesAsString, $haveFinalComma ? ',' : '', );
    }

    /**
     * @param mixed[] $value
     */
    private function correctArraySingleItemPrint(array $value, string $arrayItemAsString): string
    {
        if (count($value) !== 1) {
            return $arrayItemAsString;
        }

        if ($this->tagValueNodeConfiguration->getOriginalContent() === null) {
            return $arrayItemAsString;
        }

        // item is in the original in same format → use it
        if ($this->tagValueNodeConfiguration->originalContentContains($arrayItemAsString)) {
            return $arrayItemAsString;
        }

        // is original item used the same, just without {} brackets?
        $nakedItem = trim($arrayItemAsString, '{}');

        if (! $this->tagValueNodeConfiguration->originalContentContains('(' . $nakedItem . ')')) {
            return $arrayItemAsString;
        }

        return $nakedItem;
    }

    /**
     * @param PhpDocTagValueNode[] $tagValueNodes
     */
    private function printTagValueNodesSeparatedByComma(array $tagValueNodes): string
    {
        if ($tagValueNodes === []) {
            return '';
        }

        $itemsAsStrings = [];
        foreach ($tagValueNodes as $tagValueNode) {
            $item = '';
            if ($tagValueNode instanceof TagAwareNodeInterface) {
                $item .= $tagValueNode->getTag();
            }

            $item .= (string) $tagValueNode;

            $itemsAsStrings[] = $item;
        }

        return implode(', ', $itemsAsStrings);
    }
}
