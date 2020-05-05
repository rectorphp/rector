<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class IdTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function getShortName(): string
    {
        return '@ORM\Id';
    }

    public function toAttributeString(): string
    {
        return $this->printPhpAttributeContent();
    }
}
