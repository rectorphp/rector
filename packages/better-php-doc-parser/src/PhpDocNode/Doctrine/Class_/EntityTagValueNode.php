<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var string
     */
    private const REPOSITORY_CLASS = 'repositoryClass';

    /**
     * @var string
     */
    private const READ_ONLY = 'readOnly';

    public function removeRepositoryClass(): void
    {
        $this->items[self::REPOSITORY_CLASS] = null;
    }

    public function getShortName(): string
    {
        return '@ORM\Entity';
    }

    public function toAttributeString(): string
    {
        $items = $this->createAttributeItems();
        return $this->printItemsToAttributeString($items);
    }

    private function createAttributeItems(): array
    {
        $items = $this->items;

        if ($items[self::REPOSITORY_CLASS] !== null) {
            $items[self::REPOSITORY_CLASS] .= '::class';
        }

        if ($items[self::READ_ONLY] !== null) {
            $items[self::READ_ONLY] = $items[self::READ_ONLY] ? 'ORM\Entity::READ_ONLY' : '';
        }

        return $items;
    }
}
