<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractTagValueNode implements AttributeAwareNodeInterface, PhpDocTagValueNode
{
    use AttributeTrait;
    use PrintTagValueNodeTrait;

    /**
     * @var mixed[]
     */
    protected $items = [];

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

    protected function printItems(array $items): string
    {
        $items = $this->completeItemsQuotes($items);
        $items = $this->filterOutMissingItems($items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

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

        // replace ":" with "=" for @Route
        if ($this instanceof SymfonyRouteTagValueNode || $this instanceof DoctrineTagNodeInterface) {
            // @see https://regex101.com/r/XfKi4A/1/
            $json = Strings::replace($json, '#(\"|\w)\:(\"|\w)#', '$1=$2');
        }

        $keyPart = $this->createKeyPart($key);

        // should unquote
        if ($this->isValueWithoutQuotes($key)) {
            $json = Strings::replace($json, '#"#');
        }

        if ($this->originalContent !== null && $key !== null) {
            $json = $this->quoteKeys($item, $key, $json, $this->originalContent);
        }

        return $keyPart . $json;
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
            if ($this->originalContent !== null && Strings::endsWith($this->originalContent, '()')) {
                return '()';
            }

            return '';
        }

        // print array value to string
        foreach ($items as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $arrayItemAsString = $this->printArrayItem($value, $key);
            /** @var string $key */
            $items[$key] = $arrayItemAsString;
        }

        return sprintf(
            '(%s%s%s)',
            $this->hasNewlineAfterOpening ? PHP_EOL : '',
            implode(', ', $items),
            $this->hasNewlineBeforeClosing ? PHP_EOL : ''
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
        $this->keysByQuotedStatus = [];
        if ($originalContent === null) {
            return;
        }

        if ($this instanceof SilentKeyNodeInterface) {
            $silentKey = $this->getSilentKey();
        } else {
            $silentKey = null;
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

    protected function filterOutMissingItems(array $contentItems): array
    {
        if ($this->orderedVisibleItems === null) {
            return $contentItems;
        }

        return ArrayItemStaticHelper::filterAndSortVisibleItems($contentItems, $this->orderedVisibleItems);
    }

    private function createKeyPart(?string $key = null): string
    {
        if (empty($key)) {
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

    private function quoteKeys(array $item, string $key, string $json, string $originalContent): string
    {
        foreach (array_keys($item) as $itemKey) {
            // @see https://regex101.com/r/V7nq5D/1
            $quotedKeyPattern = '#' . $key . '={(.*?)?\"' . $itemKey . '\"(.*?)?}#';
            $isKeyQuoted = (bool) Strings::match($originalContent, $quotedKeyPattern);
            if (! $isKeyQuoted) {
                continue;
            }

            $json = Strings::replace($json, '#([^\"])' . $itemKey . '([^\"])#', '$1"' . $itemKey . '"$2');
        }
        return $json;
    }
}
