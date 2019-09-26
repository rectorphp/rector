<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ManyToOneTagValueNode extends AbstractDoctrineTagValueNode implements ToOneTagNodeInterface, InversedByNodeInterface
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\ManyToOne';

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

    public function __construct(
        string $targetEntity,
        ?array $cascade,
        string $fetch,
        ?string $inversedBy,
        ?string $originalContent,
        string $fqnTargetEntity
    ) {
        $this->targetEntity = $targetEntity;
        $this->cascade = $cascade;
        $this->fetch = $fetch;
        $this->inversedBy = $inversedBy;
        $this->fqnTargetEntity = $fqnTargetEntity;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['targetEntity'] = sprintf('targetEntity="%s"', $this->targetEntity);
        if ($this->cascade) {
            $contentItems['cascade'] = $this->printArrayItem($this->cascade, 'cascade');
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
        $this->inversedBy = null;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->targetEntity = $targetEntity;
    }
}
