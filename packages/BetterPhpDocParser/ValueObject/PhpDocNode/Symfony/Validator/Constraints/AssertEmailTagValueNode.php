<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class AssertEmailTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, PhpAttributableTagNodeInterface, SilentKeyNodeInterface
{
    public function getShortName(): string
    {
        return '@Assert\Email';
    }

    public function getSilentKey(): string
    {
        return 'choices';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return $this->filterOutMissingItems($this->items);
    }

    public function getAttributeClassName(): string
    {
        return 'Symfony\Component\Validator\Constraints\Email';
    }
}
