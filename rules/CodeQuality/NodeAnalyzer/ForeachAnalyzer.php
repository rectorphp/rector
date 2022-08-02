<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ForeachAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForAnalyzer
     */
    private $forAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeComparator $nodeComparator, \Rector\CodeQuality\NodeAnalyzer\ForAnalyzer $forAnalyzer, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeComparator = $nodeComparator;
        $this->forAnalyzer = $forAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * Matches$
     * foreach ($values as $value) {
     *      <$assigns[]> = $value;
     * }
     */
    public function matchAssignItemsOnlyForeachArrayVariable(Foreach_ $foreach) : ?Expr
    {
        if (\count($foreach->stmts) !== 1) {
            return null;
        }
        $onlyStatement = $foreach->stmts[0];
        if ($onlyStatement instanceof Expression) {
            $onlyStatement = $onlyStatement->expr;
        }
        if (!$onlyStatement instanceof Assign) {
            return null;
        }
        if (!$onlyStatement->var instanceof ArrayDimFetch) {
            return null;
        }
        if ($onlyStatement->var->dim !== null) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $onlyStatement->expr)) {
            return null;
        }
        return $onlyStatement->var->var;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function useForeachVariableInStmts(Expr $foreachedValue, Expr $singleValue, array $stmts, string $keyValueName) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use($foreachedValue, $singleValue, $keyValueName) : ?Expr {
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            // must be the same as foreach value
            if (!$this->nodeComparator->areNodesEqual($node->var, $foreachedValue)) {
                return null;
            }
            if ($this->forAnalyzer->isArrayDimFetchPartOfAssignOrArgParentCount($node)) {
                return null;
            }
            // is dim same as key value name, ...[$i]
            if (!$node->dim instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->dim, $keyValueName)) {
                return null;
            }
            return $singleValue;
        });
    }
    public function isValueVarUsed(Foreach_ $foreach, string $singularValueVarName) : bool
    {
        $isUsedInStmts = (bool) $this->betterNodeFinder->findFirst($foreach->stmts, function (Node $node) use($singularValueVarName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $singularValueVarName);
        });
        if ($isUsedInStmts) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirstNext($foreach, function (Node $node) use($singularValueVarName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $singularValueVarName);
        });
    }
}
