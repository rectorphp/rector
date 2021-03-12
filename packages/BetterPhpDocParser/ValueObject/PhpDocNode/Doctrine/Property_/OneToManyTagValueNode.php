<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class OneToManyTagValueNode extends AbstractDoctrineTagValueNode implements ToManyTagNodeInterface, MappedByNodeInterface, TypeAwareTagValueNodeInterface
{
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

        parent::__construct(
            $arrayPartPhpDocTagPrinter,
            $tagValueNodePrinter,
            $items,
            $content
        );
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
