<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;

final class PreviousVariableAssignNodeFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function find(Assign $assign): ?Node
    {
        $currentAssign = $assign;
        $variableName = $this->nodeNameResolver->getName($assign->var);

        return $this->betterNodeFinder->findFirstPrevious($assign, function (Node $node) use (
            $variableName,
            $currentAssign
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            // skip self
            if ($this->betterStandardPrinter->areSameNode($node, $currentAssign)) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, $variableName);
        });
    }
}
