<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EmbeddableTagValueNode extends AbstractDoctrineTagValueNode
{
    public function getShortName(): string
    {
        return '@ORM\Embeddable';
    }
}
