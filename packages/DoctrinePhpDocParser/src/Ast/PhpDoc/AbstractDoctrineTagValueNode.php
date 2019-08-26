<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

abstract class AbstractDoctrineTagValueNode implements PhpDocTagValueNode, AttributeAwareNodeInterface, DoctrineTagNodeInterface
{
    use AttributeTrait;

    /**
     * @var string[]
     */
    protected $orderedVisibleItems = [];

    /**
     * @param mixed[] $cascade
     */
    protected function printCascadeItem(array $cascade): string
    {
        $json = Json::encode($cascade);
        $json = Strings::replace($json, '#,#', ', ');
        $json = Strings::replace($json, '#\[(.*?)\]#', '{$1}');

        return sprintf('cascade=%s', $json);
    }

    protected function printContentItems(array $contentItems): string
    {
        $contentItems = ArrayItemStaticHelper::filterAndSortVisibleItems($contentItems, $this->orderedVisibleItems);
        if ($contentItems === []) {
            return '';
        }

        return '(' . implode(', ', $contentItems) . ')';
    }
}
