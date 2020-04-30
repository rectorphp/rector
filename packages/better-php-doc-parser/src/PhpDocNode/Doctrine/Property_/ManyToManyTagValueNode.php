<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
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
        ManyToMany $manyToMany,
        string $originalContent,
        string $fullyQualifiedTargetEntity
    ) {
        $items = get_object_vars($manyToMany);

        return new self($items, $originalContent, $fullyQualifiedTargetEntity);
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
        $items = $this->createItems(self::PRINT_TYPE_ATTRIBUTE);
        $items = $this->filterOutMissingItems($items);

        $content = $this->printPhpAttributeItems($items);
        return $this->printAttributeContent($content);
    }

    private function createItems(string $printType = self::PRINT_TYPE_ANNOTATION): array
    {
        $items = $this->items;
        if ($printType === self::PRINT_TYPE_ATTRIBUTE) {
            $items['targetEntity'] .= '::class';
        }

        return $items;
    }
}
