<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class GeneratedValueTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface, SilentKeyNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function getShortName(): string
    {
        return '@ORM\GeneratedValue';
    }

    public function toAttributeString(): string
    {
        $items = $this->filterOutMissingItems($this->items);
        $content = $this->printPhpAttributeItems($items);

        return $this->printPhpAttributeContent($content);
    }

    public function getSilentKey(): string
    {
        return 'strategy';
    }
}
