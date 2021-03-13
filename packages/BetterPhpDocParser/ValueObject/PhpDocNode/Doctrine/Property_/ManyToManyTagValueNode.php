<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;

final class ManyToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, InversedByNodeInterface, PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    private const TARGET_ENTITY = 'targetEntity';

    /**
     * @var string|null
     */
    private $fullyQualifiedTargetEntity;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items,
        ?string $content = null,
        ?string $fullyQualifiedTargetEntity = null
    ) {
        $this->fullyQualifiedTargetEntity = $fullyQualifiedTargetEntity;

        parent::__construct($arrayPartPhpDocTagPrinter, $tagValueNodePrinter, $items, $content);
    }

    public function getTargetEntity(): string
    {
        return $this->items[self::TARGET_ENTITY];
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
        $this->items[self::TARGET_ENTITY] = $targetEntity;
    }

    public function getShortName(): string
    {
        return '@ORM\ManyToMany';
    }

    public function getAttributeClassName(): string
    {
        return PhpAttributeGroupFactory::TO_BE_ANNOUNCED;
    }
}
