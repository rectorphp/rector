<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;

final class VariableReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;

    /**
     * @var NodeUsageFinder
     */
    private $nodeUsageFinder;

    /**
     * @var ReadExprAnalyzer
     */
    private $readExprAnalyzer;

    public function __construct(
        ParentScopeFinder $parentScopeFinder,
        NodeUsageFinder $nodeUsageFinder,
        ReadExprAnalyzer $readExprAnalyzer
    ) {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->readExprAnalyzer = $readExprAnalyzer;
    }

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
            if ($this->readExprAnalyzer->isReadContext($variableUsage)) {
                return true;
            }
        }

        return false;
    }
}
