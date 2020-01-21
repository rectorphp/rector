<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ManyToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, InversedByNodeInterface
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\ManyToMany';

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
     * @var mixed[]|null
     */
    private $cascade;

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
        $contentItems = [];

        $contentItems['targetEntity'] = sprintf('targetEntity="%s"', $this->targetEntity);

        if ($this->mappedBy !== null) {
            $contentItems['mappedBy'] = sprintf('mappedBy="%s"', $this->mappedBy);
        }

        if ($this->inversedBy !== null) {
            $contentItems['inversedBy'] = sprintf('inversedBy="%s"', $this->inversedBy);
        }

        if ($this->cascade !== null) {
            $contentItems['cascade'] = $this->printArrayItem($this->cascade, 'cascade');
        }

        if ($this->fetch !== null) {
            $contentItems['fetch'] = sprintf('fetch="%s"', $this->fetch);
        }

        if ($this->orphanRemoval !== null) {
            $contentItems['orphanRemoval'] = sprintf('orphanRemoval=%s', $this->orphanRemoval ? 'true' : 'false');
        }

        if ($this->indexBy !== null) {
            $contentItems['indexBy'] = sprintf('indexBy="%s"', $this->indexBy);
        }

        return $this->printContentItems($contentItems);
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
}
