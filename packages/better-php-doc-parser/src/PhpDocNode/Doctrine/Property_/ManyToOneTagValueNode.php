<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ManyToOneTagValueNode extends AbstractDoctrineTagValueNode implements ToOneTagNodeInterface, InversedByNodeInterface
{
    /**
     * @var string
     */
    private $fullyQualifiedTargetEntity;

    public function __construct($annotationOrItems, ?string $content, string $fullyQualifiedTargetEntity)
    {
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        parent::__construct($annotationOrItems, $content);
    }

    public function getTargetEntity(): ?string
    {
        return $this->items['targetEntity'];
    }

    public function getFullyQualifiedTargetEntity(): string
    {
        return $this->fullyQualifiedTargetEntity;
    }

    public function getInversedBy(): ?string
    {
        return $this->items['inversedBy'];
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
        return '@ORM\ManyToOne';
    }
}
