<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\UniqueConstraint;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class UniqueConstraintTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string|null
     */
    private $tag;

    public function __construct(
        UniqueConstraint $uniqueConstraint,
        ?string $originalContent = null,
        ?string $originalTag = null
    ) {
        $this->items = get_object_vars($uniqueConstraint);
        $this->tag = $originalTag;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function getTag(): ?string
    {
        return $this->tag ?: $this->getShortName();
    }

    public function getShortName(): string
    {
        return '@ORM\UniqueConstraint';
    }
}
