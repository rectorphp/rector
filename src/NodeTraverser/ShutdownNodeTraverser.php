<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\PropertyToClassAdder;

final class ShutdownNodeTraverser extends NodeTraverser
{
    public function __construct(PropertyToClassAdder $propertyToClassAdder)
    {
        $this->addVisitor($propertyToClassAdder);
    }
}
