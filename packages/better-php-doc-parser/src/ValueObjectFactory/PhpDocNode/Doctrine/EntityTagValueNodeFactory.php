<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\AbstractTagValueNodeFactory;

final class EntityTagValueNodeFactory extends AbstractTagValueNodeFactory
{
    public function create(): EntityTagValueNode
    {
        return $this->createFromItems([]);
    }

    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): EntityTagValueNode
    {
        return new EntityTagValueNode($this->arrayPartPhpDocTagPrinter, $this->tagValueNodePrinter, $items);
    }
}
