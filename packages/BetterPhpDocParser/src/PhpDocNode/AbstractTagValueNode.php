<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;

    /**
     * @var string[]|null
     */
    protected $orderedVisibleItems;

    /**
     * @var bool
     */
    protected $hasNewlineBeforeClosing = false;

    /**
     * @var bool
     */
    protected $hasNewlineAfterOpening = false;

    /**
     * @param mixed[] $item
     */
    protected function printArrayItem(array $item, ?string $key = null): string
    {
        $json = Json::encode($item);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#\[(.*?)\]#', '{$1}');

        // cleanup json encoded extra slashes
        $json = Strings::replace($json, '#\\\\\\\\#', '\\');

        if ($key) {
            return sprintf('%s=%s', $key, $json);
        }

        return $json;
    }

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

        return sprintf(
            '(%s%s%s)',
            $this->hasNewlineAfterOpening ? PHP_EOL : '',
            implode(', ', $contentItems),
            $this->hasNewlineBeforeClosing ? PHP_EOL : ''
        );
    }

    /**
     * @param PhpDocTagValueNode[] $tagValueNodes
     */
    protected function printNestedTag(
        array $tagValueNodes,
        string $label,
        bool $haveFinalComma = false,
        ?string $openingSpace = null,
        ?string $closingSpace = null
    ): string {
        $tagValueNodesAsString = $this->printTagValueNodesSeparatedByComma($tagValueNodes);

        if ($openingSpace === null) {
            $openingSpace = PHP_EOL . '    ';
        }

        if ($closingSpace === null) {
            $closingSpace = PHP_EOL;
        }

        return sprintf(
            '%s={%s%s%s%s}',
            $label,
            $openingSpace,
            $tagValueNodesAsString,
            $haveFinalComma ? ',' : '',
            $closingSpace
        );
    }

    /**
     * @param PhpDocTagValueNode[] $tagValueNodes
     */
    protected function printTagValueNodesSeparatedByComma(array $tagValueNodes): string
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

    protected function resolveOriginalContentSpacingAndOrder(?string $originalContent): void
    {
        if ($originalContent === null) {
            return;
        }

        $this->orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent);
        $this->hasNewlineAfterOpening = (bool) Strings::match($originalContent, '#^\(\s+#m');
        $this->hasNewlineBeforeClosing = (bool) Strings::match($originalContent, '#\s+\)$#m');
    }
}
