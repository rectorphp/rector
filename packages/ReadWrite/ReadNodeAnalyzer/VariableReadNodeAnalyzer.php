<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;

/**
 * @implements ReadNodeAnalyzerInterface<Variable>
 */
final class VariableReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    public function __construct(
        private ParentScopeFinder $parentScopeFinder,
        private NodeUsageFinder $nodeUsageFinder,
        private JustReadExprAnalyzer $justReadExprAnalyzer
    ) {
    }

    public function supports(Expr $expr): bool
    {
        return $expr instanceof Variable;
    }

    /**
     * @param Variable $expr
     */
    public function isRead(Expr $expr): bool
    {
        $parentScope = $this->parentScopeFinder->find($expr);
        if ($parentScope === null) {
            return false;
        }

        $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $expr);
        foreach ($variableUsages as $variableUsage) {
            if ($this->justReadExprAnalyzer->isReadContext($variableUsage)) {
                return true;
            }
        }

        return false;
    }
}
