<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function removeRepositoryClass(): void
    {
        $this->items['repositoryClass'] = null;
    }

    public function getShortName(): string
    {
        return '@ORM\Entity';
    }

    public function toAttributeString(): string
    {
        $items = $this->createAttributeItems();
        $items = $this->filterOutMissingItems($items);

        $content = $this->printPhpAttributeItems($items);

        return $this->printPhpAttributeContent($content);
    }

    private function createAttributeItems(): array
    {
        $items = $this->items;

        if ($items['repositoryClass'] !== null) {
            $items['repositoryClass'] .= '::class';
        }

        if ($items['readOnly'] !== null) {
            $items['readOnly'] = $items['readOnly'] ? 'ORM\Entity::READ_ONLY' : '';
        }

        return $items;
    }
}
