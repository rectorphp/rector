<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;
final class VariableReadNodeAnalyzer implements \Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface
{
    /**
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @var \Rector\ReadWrite\NodeFinder\NodeUsageFinder
     */
    private $nodeUsageFinder;
    /**
     * @var \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer
     */
    private $justReadExprAnalyzer;
    public function __construct(\Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\ReadWrite\NodeFinder\NodeUsageFinder $nodeUsageFinder, \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer $justReadExprAnalyzer)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function supports($node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\Variable;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function isRead($node) : bool
    {
        $parentScope = $this->parentScopeFinder->find($node);
        if ($parentScope === null) {
            return \false;
        }
        $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $node);
        foreach ($variableUsages as $variableUsage) {
            if ($this->justReadExprAnalyzer->isReadContext($variableUsage)) {
                return \true;
            }
        }
        return \false;
    }
}
