<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EmbeddableTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __construct(string $originalContent)
    {
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        return $this->originalContent;
    }

    public function getShortName(): string
    {
        return '@ORM\Embeddable';
    }
}
