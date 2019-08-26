<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\InversedByNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToOneTagNodeInterface;

final class ManyToOneTagValueNode extends AbstractDoctrineTagValueNode implements ToOneTagNodeInterface, InversedByNodeInterface
{
    /**
     * @var string
     */
    private $targetEntity;

    /**
     * @var array|null
     */
    private $cascade;

    /**
     * @var string
     */
    private $fetch;

    /**
     * @var string|null
     */
    private $inversedBy;

    /**
     * @var string
     */
    private $fqnTargetEntity;

    /**
     * @param string[] $orderedVisibleItems
     */
    public function __construct(
        string $targetEntity,
        ?array $cascade,
        string $fetch,
        ?string $inversedBy,
        array $orderedVisibleItems,
        string $fqnTargetEntity
    ) {
        $this->orderedVisibleItems = $orderedVisibleItems;
        $this->targetEntity = $targetEntity;
        $this->cascade = $cascade;
        $this->fetch = $fetch;
        $this->inversedBy = $inversedBy;
        $this->fqnTargetEntity = $fqnTargetEntity;
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['targetEntity'] = sprintf('targetEntity="%s"', $this->targetEntity);
        if ($this->cascade) {
            $contentItems['cascade'] = $this->printCascadeItem($this->cascade);
        }
        $contentItems['fetch'] = sprintf('fetch="%s"', $this->fetch);
        $contentItems['inversedBy'] = sprintf('inversedBy="%s"', $this->inversedBy);

        return $this->printContentItems($contentItems);
    }

    public function getTargetEntity(): ?string
    {
        return $this->targetEntity;
    }

    public function getFqnTargetEntity(): string
    {
        return $this->fqnTargetEntity;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function removeInversedBy(): void
    {
        $this->orderedVisibleItems = ArrayItemStaticHelper::removeItemFromArray(
            $this->orderedVisibleItems,
            'inversedBy'
        );

        $this->inversedBy = null;
    }
}
