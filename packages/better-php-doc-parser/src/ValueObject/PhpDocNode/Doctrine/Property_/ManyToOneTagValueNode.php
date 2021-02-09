<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ManyToOneTagValueNode extends AbstractDoctrineTagValueNode implements ToOneTagNodeInterface, InversedByNodeInterface
{
    /**
     * @var string
     */
    private $fullyQualifiedTargetEntity;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items, ?string $content, string $fullyQualifiedTargetEntity
    ) {
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        parent::__construct($arrayPartPhpDocTagPrinter, $tagValueNodePrinter, $items, $content);
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
