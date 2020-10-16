<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;

final class VariableReadNodeAnalyzer extends AbstractReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    public function supports(Node $node): bool
    {
        return $node instanceof Variable;
    }

    /**
     * @param Variable $node
     */
    public function isRead(Node $node): bool
    {
        $parentScope = $this->parentScopeFinder->find($node);
        if ($parentScope === null) {
            return false;
        }

        $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $node);
        foreach ($variableUsages as $variableUsage) {
            if ($this->isCurrentContextRead($variableUsage)) {
                return true;
            }
        }

        return false;
    }
}
