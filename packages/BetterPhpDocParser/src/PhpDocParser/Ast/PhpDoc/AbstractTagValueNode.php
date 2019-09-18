<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;

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
    protected function printArrayItem(array $item, string $key): string
    {
        $json = Json::encode($item);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#\[(.*?)\]#', '{$1}');

        // cleanup json encoded extra slashes
        $json = Strings::replace($json, '#\\\\\\\\#', '\\');

        return sprintf('%s=%s', $key, $json);
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
