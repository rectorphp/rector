<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class CustomIdGeneratorTagValueNode extends AbstractDoctrineTagValueNode
{
    public function getShortName(): string
    {
        return '@ORM\CustomIdGenerator';
    }
}
