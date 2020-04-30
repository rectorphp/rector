<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class CustomIdGeneratorTagValueNode extends AbstractDoctrineTagValueNode
{
    public function getShortName(): string
    {
        return '@ORM\CustomIdGenerator';
    }
}
