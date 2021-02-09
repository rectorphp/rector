<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\AbstractTagValueNodeFactory;

final class ColumnTagValueNodeFactory extends AbstractTagValueNodeFactory
{
    public function create(): ColumnTagValueNode
    {
        return $this->createFromItems([]);
    }

    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): ColumnTagValueNode
    {
        return new ColumnTagValueNode($this->arrayPartPhpDocTagPrinter, $this->tagValueNodePrinter, $items);
    }
}
