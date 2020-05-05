<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class ManyToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, InversedByNodeInterface, PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var string|null
     */
    private $fullyQualifiedTargetEntity;

    public function __construct(
        array $items,
        ?string $content = null,
        ?string $fullyQualifiedTargetEntity = null
    ) {
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        parent::__construct($items, $content);
    }

    public function getTargetEntity(): string
    {
        return $this->items['targetEntity'];
    }

    public function getFullyQualifiedTargetEntity(): ?string
    {
        return $this->fullyQualifiedTargetEntity;
    }

    public function getInversedBy(): ?string
    {
        return $this->items['inversedBy'];
    }

    public function getMappedBy(): ?string
    {
        return $this->items['mappedBy'];
    }

    public function removeMappedBy(): void
    {
        $this->items['mappedBy'] = null;
    }

    public function removeInversedBy(): void
    {
        $this->items['inversedBy'] = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->items['targetEntity'] = $targetEntity;
    }

    public function getShortName(): string
    {
        return '@ORM\ManyToMany';
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
        $items['targetEntity'] .= '::class';

        return $items;
    }
}
