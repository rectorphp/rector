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
     * @var string
     */
    private $targetEntity;

    /**
     * @var string|null
     */
    private $mappedBy;

    /**
     * @var string|null
     */
    private $inversedBy;

    /**
     * @var string|null
     */
    private $fetch;

    /**
     * @var bool|null
     */
    private $orphanRemoval;

    /**
     * @var string|null
     */
    private $indexBy;

    /**
     * @var string|null
     */
    private $fqnTargetEntity;

    /**
     * @var mixed[]|null
     */
    private $cascade;

    public function __construct(
        string $targetEntity,
        ?string $mappedBy = null,
        ?string $inversedBy = null,
        ?array $cascade = null,
        ?string $fetch = null,
        ?bool $orphanRemoval = null,
        ?string $indexBy = null,
        ?string $originalContent = null,
        ?string $fqnTargetEntity = null
    ) {
        $this->targetEntity = $targetEntity;
        $this->mappedBy = $mappedBy;
        $this->inversedBy = $inversedBy;
        $this->cascade = $cascade;
        $this->fetch = $fetch;
        $this->orphanRemoval = $orphanRemoval;
        $this->indexBy = $indexBy;
        $this->fqnTargetEntity = $fqnTargetEntity;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }
    }

    public function __toString(): string
    {
        $items = $this->createItems();
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function getFqnTargetEntity(): ?string
    {
        return $this->fqnTargetEntity;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }

    public function removeMappedBy(): void
    {
        $this->mappedBy = null;
    }

    public function removeInversedBy(): void
    {
        $this->inversedBy = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->targetEntity = $targetEntity;
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
        $contentItems = [];

        if ($printType === self::PRINT_TYPE_ATTRIBUTE) {
            $contentItems['targetEntity'] = $this->targetEntity . '::class';
        } else {
            $contentItems['targetEntity'] = sprintf('"%s"', $this->targetEntity);
        }

        if ($this->mappedBy !== null) {
            $contentItems['mappedBy'] = sprintf('"%s"', $this->mappedBy);
        }

        if ($this->inversedBy !== null) {
            $contentItems['inversedBy'] = sprintf('"%s"', $this->inversedBy);
        }

        if ($this->cascade !== null) {
            $contentItems['cascade'] = $this->cascade;
        }

        if ($this->fetch !== null) {
            $contentItems['fetch'] = sprintf('"%s"', $this->fetch);
        }

        if ($this->orphanRemoval !== null) {
            $contentItems['orphanRemoval'] = $this->orphanRemoval ? 'true' : 'false';
        }

        if ($this->indexBy !== null) {
            $contentItems['indexBy'] = sprintf('"%s"', $this->indexBy);
        }

        return $contentItems;
    }
}
