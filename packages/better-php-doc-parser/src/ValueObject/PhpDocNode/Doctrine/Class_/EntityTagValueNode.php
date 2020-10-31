<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    private const REPOSITORY_CLASS = 'repositoryClass';

    public function removeRepositoryClass(): void
    {
        $this->items[self::REPOSITORY_CLASS] = null;
    }

    public function getShortName(): string
    {
        return '@ORM\Entity';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return $this->filterOutMissingItems($this->items);
    }

    public function getAttributeClassName(): string
    {
        return 'TBA';
    }
}
