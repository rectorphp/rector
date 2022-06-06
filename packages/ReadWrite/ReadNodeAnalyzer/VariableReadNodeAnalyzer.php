<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\ReadNodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\NodeNestingScope\ParentScopeFinder;
use RectorPrefix20220606\Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use RectorPrefix20220606\Rector\ReadWrite\NodeFinder\NodeUsageFinder;
/**
 * @implements ReadNodeAnalyzerInterface<Variable>
 */
final class VariableReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ParentScopeFinder
     */
    private $parentScopeFinder;
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeFinder\NodeUsageFinder
     */
    private $nodeUsageFinder;
    /**
     * @readonly
     * @var \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer
     */
    private $justReadExprAnalyzer;
    public function __construct(ParentScopeFinder $parentScopeFinder, NodeUsageFinder $nodeUsageFinder, JustReadExprAnalyzer $justReadExprAnalyzer)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
    }
    public function supports(Expr $expr) : bool
    {
        return $expr instanceof Variable;
    }
    /**
     * @param Variable $expr
     */
    public function isRead(Expr $expr) : bool
    {
        $parentScope = $this->parentScopeFinder->find($expr);
        if ($parentScope === null) {
            return \false;
        }
        $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $expr);
        foreach ($variableUsages as $variableUsage) {
            if ($this->justReadExprAnalyzer->isReadContext($variableUsage)) {
                return \true;
            }
        }
        return \false;
    }
}
