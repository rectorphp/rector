<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\OneToOne;
use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class OneToOneTagValueNode extends AbstractDoctrineTagValueNode implements ToOneTagNodeInterface, MappedByNodeInterface, InversedByNodeInterface
{
    /**
     * @var string|null
     */
    private $fullyQualifiedTargetEntity;

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(
        array $items,
        ?string $originalContent = null,
        ?string $fullyQualifiedTargetEntity = null
    ) {
        $this->items = $items;
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public static function createFromAnnotationAndOriginalContent(
        OneToOne $oneToOne,
        string $originalContent,
        ?string $fullyQualifiedTargetEntity = null
    ) {
        $items = get_object_vars($oneToOne);

        return new self($items, $originalContent, $fullyQualifiedTargetEntity);
    }

    public function getTargetEntity(): ?string
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

    public function removeInversedBy(): void
    {
        $this->items['inversedBy'] = null;
    }

    public function removeMappedBy(): void
    {
        $this->items['mappedBy'] = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->items['targetEntity'] = $targetEntity;
    }

    public function getShortName(): string
    {
        return '@ORM\OneToOne';
    }
}
