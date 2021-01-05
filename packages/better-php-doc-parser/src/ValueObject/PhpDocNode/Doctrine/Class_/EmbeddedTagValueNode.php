<?php
declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class EmbeddedTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $fullyQualifiedClassName;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items, string $fullyQualifiedClassName)
    {
        parent::__construct($items);

        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
    }

    public function getShortName(): string
    {
        return '@ORM\Embedded';
    }

    public function getClass(): string
    {
        return $this->items['class'];
    }

    public function getColumnPrefix(): ?string
    {
        return $this->items['columnPrefix'];
    }
}
