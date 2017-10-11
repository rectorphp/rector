<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\PropertyToClassAdder;
use Rector\NodeVisitor\StatementToMethodAdder;

final class ShutdownNodeTraverser extends NodeTraverser
{
    public function __construct(
        PropertyToClassAdder $propertyToClassAdder,
        StatementToMethodAdder $statementToMethodAdder
    ) {
        $this->addVisitor($propertyToClassAdder);
        $this->addVisitor($statementToMethodAdder);
    }
}
