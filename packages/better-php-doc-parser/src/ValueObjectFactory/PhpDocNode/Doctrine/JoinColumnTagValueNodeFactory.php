<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\AbstractTagValueNodeFactory;

final class JoinColumnTagValueNodeFactory extends AbstractTagValueNodeFactory
{
    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): JoinColumnTagValueNode
    {
        return new JoinColumnTagValueNode($this->arrayPartPhpDocTagPrinter, $this->tagValueNodePrinter, $items);
    }
}
