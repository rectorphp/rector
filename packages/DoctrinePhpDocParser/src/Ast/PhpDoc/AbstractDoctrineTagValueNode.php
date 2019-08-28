<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

abstract class AbstractDoctrineTagValueNode implements AttributeAwareNodeInterface, DoctrineTagNodeInterface
{
    use AttributeTrait;

    /**
     * @var string[]|null
     */
    protected $orderedVisibleItems;

    /**
     * @param mixed[] $item
     */
    protected function printArrayItem(array $item, string $key): string
    {
        $json = Json::encode($item);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#\[(.*?)\]#', '{$1}');

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
