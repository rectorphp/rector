<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\Embeddable;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EmbeddableTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __construct(Embeddable $embeddable, string $originalContent)
    {
        $this->items = get_object_vars($embeddable);
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function getShortName(): string
    {
        return '@ORM\Embeddable';
    }
}
