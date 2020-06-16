<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PartPhpDocTagPrinter\Behavior\ArrayPartPhpDocTagPrinterTrait;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;
use Rector\Core\Exception\ShouldNotHappenException;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;
    use PrintTagValueNodeTrait;
    use ArrayPartPhpDocTagPrinterTrait;

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * @var TagValueNodeConfiguration
     */
    protected $tagValueNodeConfiguration;

    public function __construct(array $items, ?string $originalContent = null)
    {
        $this->items = $items;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
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
    protected function printNestedTag(
        array $tagValueNodes,
        bool $haveFinalComma,
        ?string $openingSpace,
        ?string $closingSpace
    ): string {
        $tagValueNodesAsString = $this->printTagValueNodesSeparatedByComma($tagValueNodes);

        if ($openingSpace === null) {
            $openingSpace = PHP_EOL . '    ';
        }

        if ($closingSpace === null) {
            $closingSpace = PHP_EOL;
        }

        return sprintf(
            '{%s%s%s%s}',
            $openingSpace,
            $tagValueNodesAsString,
            $haveFinalComma ? ',' : '',
            $closingSpace
        );
    }

    protected function resolveOriginalContentSpacingAndOrder(?string $originalContent): void
    {
        if ($this instanceof SilentKeyNodeInterface) {
            $silentKey = $this->getSilentKey();
        } else {
            $silentKey = null;
        }

        $tagValueNodeConfigurationFactory = new TagValueNodeConfigurationFactory();

        // prevent override
        if ($this->tagValueNodeConfiguration !== null) {
            throw new ShouldNotHappenException();
        }

        $this->tagValueNodeConfiguration = $tagValueNodeConfigurationFactory->createFromOriginalContent(
            $originalContent,
            $silentKey
        );
    }

    protected function filterOutMissingItems(array $contentItems): array
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

    private function correctArraySingleItemPrint($value, string $arrayItemAsString): string
    {
        if (count($value) !== 1) {
            return $arrayItemAsString;
        }

        if ($this->tagValueNodeConfiguration->getOriginalContent() === null) {
            return $arrayItemAsString;
        }

        // item is in the original in same format → use it
        if (Strings::contains($this->tagValueNodeConfiguration->getOriginalContent(), $arrayItemAsString)) {
            return $arrayItemAsString;
        }

        // is original item used the same, just without {} brackets?
        $nakedItem = trim($arrayItemAsString, '{}');

        if (! Strings::contains($this->tagValueNodeConfiguration->getOriginalContent(), '(' . $nakedItem . ')')) {
            return $arrayItemAsString;
        }

        return $nakedItem;
    }

    private function shouldPrintEmptyBrackets(): bool
    {
        // @todo decouple
        if ($this->tagValueNodeConfiguration->getOriginalContent() !== null && Strings::endsWith(
            $this->tagValueNodeConfiguration->getOriginalContent(),
            '()'
        )) {
            return true;
        }

        return $this->tagValueNodeConfiguration->hasOpeningBracket() && $this->tagValueNodeConfiguration->hasClosingBracket();
    }
}
