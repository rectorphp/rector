<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class PreviousVariableAssignNodeFinder
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private NodeComparator $nodeComparator
    ) {
    }

    public function find(Assign $assign): ?Node
    {
        $currentAssign = $assign;

        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }

        return $this->betterNodeFinder->findFirstPrevious($assign, function (Node $node) use (
            $variableName,
            $currentAssign
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            // skip self
            if ($this->nodeComparator->areSameNode($node, $currentAssign)) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, $variableName);
        });
    }
}
