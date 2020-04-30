<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\OneToMany;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class OneToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, TypeAwareTagValueNodeInterface
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

    public static function createFromAnnotationAndContent(
        OneToMany $oneToMany,
        string $originalContent,
        ?string $fullyQualifiedTargetEntity = null
    ) {
        $items = get_object_vars($oneToMany);

        return new self($items, $originalContent, $fullyQualifiedTargetEntity);
    }

    public function getTargetEntity(): string
    {
        return $this->items['targetEntity'];
    }

    public function getMappedBy(): ?string
    {
        return $this->items['mappedBy'];
    }

    public function removeMappedBy(): void
    {
        $this->items['mappedBy'] = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->items['targetEntity'] = $targetEntity;
    }

    public function getFullyQualifiedTargetEntity(): ?string
    {
        return $this->fullyQualifiedTargetEntity;
    }

    public function getShortName(): string
    {
        return '@ORM\OneToMany';
    }
}
