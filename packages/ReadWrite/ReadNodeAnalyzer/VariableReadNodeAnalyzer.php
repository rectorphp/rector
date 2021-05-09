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
    public function __construct(\Rector\NodeNestingScope\ParentScopeFinder $parentScopeFinder, \Rector\ReadWrite\NodeFinder\NodeUsageFinder $nodeUsageFinder, \Rector\ReadWrite\ReadNodeAnalyzer\ReadExprAnalyzer $readExprAnalyzer)
    {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->readExprAnalyzer = $readExprAnalyzer;
    }
    public function supports(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\Variable;
    }
    /**
     * @param Variable $node
     */
    public function isRead(\PhpParser\Node $node) : bool
    {
        $parentScope = $this->parentScopeFinder->find($node);
        if ($parentScope === null) {
            return \false;
        }
        $variableUsages = $this->nodeUsageFinder->findVariableUsages((array) $parentScope->stmts, $node);
        foreach ($variableUsages as $variableUsage) {
            if ($this->readExprAnalyzer->isReadContext($variableUsage)) {
                return \true;
            }
        }
        return \false;
    }
}
