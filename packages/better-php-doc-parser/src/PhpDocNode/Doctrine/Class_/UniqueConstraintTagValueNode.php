<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

final class UniqueConstraintTagValueNode extends AbstractIndexTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\UniqueConstraint';

    public function getTag(): ?string
    {
        return $this->tag ?: self::SHORT_NAME;
    }
}
