<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\ExpressionRemover;
use Rector\NodeVisitor\PropertyToClassAdder;

final class ShutdownNodeTraverser extends NodeTraverser
{
    public function __construct(PropertyToClassAdder $propertyToClassAdder, ExpressionRemover $expressionRemover)
    {
        $this->addVisitor($propertyToClassAdder);
        $this->addVisitor($expressionRemover);
    }
}
