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

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(?Entity $entity = null, ?string $originalContent = null)
    {
        if ($entity !== null) {
            $this->items = get_object_vars($entity);
        }

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
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
        $items = $this->createItems(self::PRINT_TYPE_ATTRIBUTE);
        $items = $this->filterOutMissingItems($items);

        $content = $this->printPhpAttributeItems($items);

        return $this->printAttributeContent($content);
    }

    private function createItems(string $printType = self::PRINT_TYPE_ANNOTATION): array
    {
        $items = $this->items;

        if ($printType === self::PRINT_TYPE_ATTRIBUTE) {
            if ($items['repositoryClass'] !== null) {
                $items['repositoryClass'] .= '::class';
            }

            if ($items['readOnly'] !== null) {
                $items['readOnly'] = $items['readOnly'] ? 'ORM\Entity::READ_ONLY' : '';
            }
        }

        return $items;
    }
}
