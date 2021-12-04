<?php

declare (strict_types=1);
namespace Rector\ReadWrite\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;
final class NodeUsageFinder
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder $scopeAwareNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    public function findVariableUsages(array $nodes, \PhpParser\Node\Expr\Variable $variable) : array
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return [];
        }
        return $this->betterNodeFinder->find($nodes, function (\PhpParser\Node $node) use($variable, $variableName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            if ($node === $variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $variableName);
        });
    }
    public function findPreviousForeachNodeUsage(\PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node
    {
        return $this->scopeAwareNodeFinder->findParent($foreach, function (\PhpParser\Node $node) use($expr) : bool {
            // skip itself
            if ($node === $expr) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $expr);
        }, [\PhpParser\Node\Stmt\Foreach_::class]);
    }
}
