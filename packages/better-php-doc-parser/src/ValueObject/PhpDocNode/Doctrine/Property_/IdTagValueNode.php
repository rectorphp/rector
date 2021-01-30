<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;

final class IdTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    public function getShortName(): string
    {
        return '@ORM\Id';
    }

    public function getAttributeClassName(): string
    {
        return PhpAttributeGroupFactory::TO_BE_ANNOUNCED;
    }
}
