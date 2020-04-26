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
use Rector\Core\Exception\ShouldNotHappenException;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;

    /**
     * @var bool
     */
    protected $hasNewlineAfterOpening = false;

    /**
     * @var bool
     */
    protected $hasNewlineBeforeClosing = false;

    /**
     * @var string|null
     */
    protected $originalContent;

    /**
     * @var bool
     */
    protected $hasOpeningBracket = false;

    /**
     * @var bool
     */
    protected $hasClosingBracket = false;

    /**
     * @var string[]|null
     */
    protected $orderedVisibleItems;

    /**
     * @var bool
     */
    private $isSilentKeyExplicit = true;

    /**
     * @var string|null
     */
    private $silentKey;

    /**
     * @var bool[]
     */
    private $keysByQuotedStatus = [];

    /**
     * @param mixed[] $item
     */
    protected function printArrayItem(array $item, ?string $key = null): string
    {
        $json = Json::encode($item);

        // separate by space only items separated by comma, not in "quotes"
        $json = Strings::replace($json, '#,#', ', ');
        // @see https://regex101.com/r/C2fDQp/2
        $json = Strings::replace($json, '#("[^",]+)(\s+)?,(\s+)?([^"]+")#', '$1,$4');

        // change brackets from json to annotations
        $json = Strings::replace($json, '#^\[(.*?)\]$#', '{$1}');

        // cleanup json encoded extra slashes
        $json = Strings::replace($json, '#\\\\\\\\#', '\\');

        $keyPart = $this->createKeyPart($key);

        // should unqote
        if ($this->isValueWithoutQuotes($key)) {
            // @todo resolve per key item
            $json = Strings::replace($json, '#"#');
        }

        return $keyPart . $json;
    }

    /**
     * @param mixed[] $item
     */
    protected function printArrayItemWithSeparator(array $item, ?string $key = null, string $separator = ''): string
    {
        $content = $this->printArrayItem($item, $key);

        return Strings::replace($content, '#:#', $separator);
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
            if ($this->originalContent !== null && Strings::endsWith($this->originalContent, '()')) {
                return '()';
            }

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
            '%s={%s%s%s%s}',
            $label,
            $openingSpace,
            $tagValueNodesAsString,
            $haveFinalComma ? ',' : '',
            $closingSpace
        );
    }

    protected function resolveOriginalContentSpacingAndOrder(?string $originalContent, ?string $silentKey = null): void
    {
        $this->keysByQuotedStatus = [];

        if ($originalContent === null) {
            return;
        }

        $this->originalContent = $originalContent;
        $this->orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent, $silentKey);

        $this->hasNewlineAfterOpening = (bool) Strings::match($originalContent, '#^(\(\s+|\n)#m');
        $this->hasNewlineBeforeClosing = (bool) Strings::match($originalContent, '#(\s+\)|\n(\s+)?)$#m');

        $this->hasOpeningBracket = (bool) Strings::match($originalContent, '#^\(#');
        $this->hasClosingBracket = (bool) Strings::match($originalContent, '#\)$#');

        foreach ($this->orderedVisibleItems as $orderedVisibleItem) {
            $this->keysByQuotedStatus[$orderedVisibleItem] = $this->isKeyQuoted(
                $originalContent,
                $orderedVisibleItem,
                $silentKey
            );
        }

        $this->silentKey = $silentKey;
        $this->isSilentKeyExplicit = (bool) Strings::contains($originalContent, sprintf('%s=', $silentKey));
    }

    protected function printValueWithOptionalQuotes(string $key, ...$values): string
    {
        // pick first non-null value
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            break;
        }

        if (! isset($value)) {
            throw new ShouldNotHappenException();
        }

        if (is_array($value)) {
            return $this->printArrayItem($value, $key);
        }

        $keyPart = $this->createKeyPart($key);

        // quote by default
        if (! isset($this->keysByQuotedStatus[$key]) || (isset($this->keysByQuotedStatus[$key]) && $this->keysByQuotedStatus[$key])) {
            return sprintf('%s"%s"', $keyPart, $value);
        }

        return $keyPart . $value;
    }

    private function createKeyPart(?string $key): string
    {
        if ($key === null) {
            return '';
        }

        if ($key === $this->silentKey && ! $this->isSilentKeyExplicit) {
            return '';
        }

        return $key . '=';
    }

    private function isValueWithoutQuotes(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        if (! array_key_exists($key, $this->keysByQuotedStatus)) {
            return false;
        }

        return ! $this->keysByQuotedStatus[$key];
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

    private function isKeyQuoted(string $originalContent, string $key, ?string $silentKey): bool
    {
        $escapedKey = preg_quote($key, '#');

        $quotedKeyPattern = $this->createQuotedKeyPattern($silentKey, $key, $escapedKey);
        if ((bool) Strings::match($originalContent, $quotedKeyPattern)) {
            return true;
        }

        // @see https://regex101.com/r/VgvK8C/5/
        $quotedArrayPattern = sprintf('#%s=\{"(.*)"\}|\{"(.*)"\}#', $escapedKey);

        return (bool) Strings::match($originalContent, $quotedArrayPattern);
    }

    private function createQuotedKeyPattern(?string $silentKey, string $key, string $escapedKey): string
    {
        if ($silentKey === $key) {
            // @see https://regex101.com/r/VgvK8C/4/
            return sprintf('#(%s=")|\("#', $escapedKey);
        }

        // @see https://regex101.com/r/VgvK8C/3/
        return sprintf('#%s="#', $escapedKey);
    }
}
