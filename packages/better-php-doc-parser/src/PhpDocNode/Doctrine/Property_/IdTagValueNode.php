<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class IdTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __toString(): string
    {
        return '';
    }

    public function getShortName(): string
    {
        return '@ORM\Id';
    }
}
