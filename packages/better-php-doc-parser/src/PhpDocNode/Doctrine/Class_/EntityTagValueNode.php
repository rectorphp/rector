<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\Entity;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function __construct(?Entity $entity = null, ?string $originalContent = null)
    {
        if ($entity !== null) {
            $this->items = get_object_vars($entity);
        }

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

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

        return $this->printAttributeContent($content);
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
