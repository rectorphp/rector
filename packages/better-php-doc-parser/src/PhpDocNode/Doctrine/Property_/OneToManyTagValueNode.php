<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class OneToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, TypeAwareTagValueNodeInterface
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\OneToMany';

    /**
     * @var string|null
     */
    private $mappedBy;

    /**
     * @var string
     */
    private $targetEntity;

    /**
     * @var string|null
     */
    private $fetch;

    /**
     * @var bool|null
     */
    private $orphanRemoval = false;

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
        ?string $mappedBy = null,
        string $targetEntity,
        ?array $cascade = null,
        ?string $fetch = null,
        ?bool $orphanRemoval = null,
        ?string $indexBy = null,
        ?string $originalContent = null,
        ?string $fqnTargetEntity = null
    ) {
        $this->mappedBy = $mappedBy;
        $this->targetEntity = $targetEntity;
        $this->cascade = $cascade;
        $this->fetch = $fetch;
        $this->orphanRemoval = $orphanRemoval;
        $this->indexBy = $indexBy;
        $this->fqnTargetEntity = $fqnTargetEntity;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->mappedBy !== null) {
            $contentItems['mappedBy'] = sprintf('mappedBy="%s"', $this->mappedBy);
        }
        $contentItems['targetEntity'] = sprintf('targetEntity="%s"', $this->targetEntity);

        if ($this->cascade) {
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

    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }

    public function removeMappedBy(): void
    {
        $this->mappedBy = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->targetEntity = $targetEntity;
    }
}
