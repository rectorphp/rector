<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\AbstractTagValueNodeFactory;

final class SymfonyRouteTagValueNodeFactory extends AbstractTagValueNodeFactory
{
    public function create(): SymfonyRouteTagValueNode
    {
        return $this->createFromItems([]);
    }

    /**
     * @param array<string, mixed> $items
     */
    public function createFromItems(array $items): SymfonyRouteTagValueNode
    {
        return new SymfonyRouteTagValueNode($this->arrayPartPhpDocTagPrinter, $this->tagValueNodePrinter, $items);
    }
}
