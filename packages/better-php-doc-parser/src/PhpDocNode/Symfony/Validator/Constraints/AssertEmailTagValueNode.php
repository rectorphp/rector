<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class AssertEmailTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, PhpAttributableTagNodeInterface, SilentKeyNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function getShortName(): string
    {
        return '@Assert\Email';
    }

    public function toAttributeString(): string
    {
        return $this->printItemsToAttributeAsArrayString($this->items);
    }

    public function getSilentKey(): string
    {
        return 'choices';
    }
}
