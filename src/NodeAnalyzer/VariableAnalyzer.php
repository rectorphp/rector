<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Static_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class VariableAnalyzer
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    public function isStatic(Variable $variable): bool
    {
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (Node $n) use (
            $variable
        ): bool {
            if (! $n instanceof Static_) {
                return false;
            }

            foreach ($n->vars as $staticVar) {
                if ($this->nodeComparator->areNodesEqual($staticVar->var, $variable)) {
                    return true;
                }
            }

            return false;
        });
    }
}
