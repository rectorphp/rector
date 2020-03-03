<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

final class IndexTagValueNode extends AbstractIndexTagValueNode
{
    public function getTag(): ?string
    {
        return $this->tag ?: $this->getShortName();
    }

    public function getShortName(): string
    {
        return '@ORM\Index';
    }
}
