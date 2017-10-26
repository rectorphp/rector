<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\NodeRemover;
use Rector\NodeVisitor\PropertyToClassAdder;

final class ShutdownNodeTraverser extends NodeTraverser
{
    public function __construct(PropertyToClassAdder $propertyToClassAdder, NodeRemover $expressionRemover)
    {
        $this->addVisitor($propertyToClassAdder);
        $this->addVisitor($expressionRemover);
    }
}
