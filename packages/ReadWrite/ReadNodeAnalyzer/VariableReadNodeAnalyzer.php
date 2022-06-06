<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;
/**
 * @implements ReadNodeAnalyzerInterface<Variable>
 */
final class VariableReadNodeAnalyzer implements \Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface
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
    public function __construct(\Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\ReadWrite\NodeFinder\NodeUsageFinder $nodeUsageFinder, \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer $justReadExprAnalyzer)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
    }
    public function supports(\PhpParser\Node\Expr $expr) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\Variable;
    }
    /**
     * @param Variable $expr
     */
    public function isRead(\PhpParser\Node\Expr $expr) : bool
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
