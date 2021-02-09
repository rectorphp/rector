<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;
use Rector\BetterPhpDocParser\ValueObjectFactory\TagValueNodeConfigurationFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\PackageBuilder\Php\TypeChecker;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * @var TagValueNodeConfiguration
     */
    protected $tagValueNodeConfiguration;

    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    protected $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    protected $tagValueNodePrinter;

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items = [],
        ?string $originalContent = null
    ) {
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;

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
     * @param mixed[] $items
     */
    protected function printItems(array $items): string
    {
        $items = $this->tagValueNodePrinter->completeItemsQuotes($this->tagValueNodeConfiguration, $items);
        $items = $this->filterOutMissingItems($items);
        $items = $this->tagValueNodePrinter->makeKeysExplicit($items, $this->tagValueNodeConfiguration);

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

            $arrayItemAsString = $this->arrayPartPhpDocTagPrinter->printArrayItem(
                $value,
                $key,
                $this->tagValueNodeConfiguration
            );
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
        $tagValueNodeConfigurationFactory = new TagValueNodeConfigurationFactory(new TypeChecker());

        // prevent override
        if ($this->tagValueNodeConfiguration !== null) {
            throw new ShouldNotHappenException();
        }

        $this->tagValueNodeConfiguration = $tagValueNodeConfigurationFactory->createFromOriginalContent(
            $originalContent,
            $this
        );
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
        if (! $this->tagValueNodeConfiguration->hasOpeningBracket()) {
            return false;
        }
        return $this->tagValueNodeConfiguration->hasClosingBracket();
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
