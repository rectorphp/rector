<?php
declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EmbeddedTagValueNode extends AbstractDoctrineTagValueNode implements DoctrineRelationTagValueNodeInterface
{
    /**
     * @var string
     */
    private $fullyQualifiedClassName;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items,
        ?string $originalContent,
        string $fullyQualifiedClassName
    ) {
        parent::__construct(
            $arrayPartPhpDocTagPrinter,
            $tagValueNodePrinter,
            $items,
            $originalContent
        );

        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
    }

    public function getShortName(): string
    {
        return '@ORM\Embedded';
    }

    public function getColumnPrefix(): ?string
    {
        return $this->items['columnPrefix'];
    }

    public function getTargetEntity(): ?string
    {
        return $this->items['class'];
    }

    public function getFullyQualifiedTargetEntity(): string
    {
        return $this->fullyQualifiedClassName;
    }

    public function changeTargetEntity(string $targetEntity): void
    {
        $this->items['class'] = $targetEntity;
    }
}
