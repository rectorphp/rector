<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class NextVariableUsageNodeFinder
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var UsedVariableNameAnalyzer
     */
    private $usedVariableNameAnalyzer;

    /**
     * @var CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ParentScopeFinder $parentScopeFinder,
        NodeComparator $nodeComparator,
        UsedVariableNameAnalyzer $usedVariableNameAnalyzer,
        CompactFuncCallAnalyzer $compactFuncCallAnalyzer
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parentScopeFinder = $parentScopeFinder;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->usedVariableNameAnalyzer = $usedVariableNameAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }

    public function find(Assign $assign): ?Node
    {
        $scopeNode = $this->parentScopeFinder->find($assign);
        if ($scopeNode === null) {
            return null;
        }

        /** @var Variable $expr */
        $expr = $assign->var;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $scopeNode->stmts, function (
            Node $currentNode
        ) use ($expr, &$nextUsageOfVariable, &$flag): ?int {
            // used above the assign
            if ($currentNode->getStartTokenPos() < $expr->getStartTokenPos()) {
                return null;
            }

            // skip self
            if ($this->nodeComparator->areSameNode($currentNode, $expr)) {
                return null;
            }

            if (! $this->nodeComparator->areNodesEqual($currentNode, $expr)) {
                return null;
            }

            $currentNodeParent = $currentNode->getAttribute(AttributeKey::PARENT_NODE);

            if ($currentNodeParent instanceof Assign && ! $this->hasInParentExpression($currentNodeParent, $expr)) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $nextUsageOfVariable = $currentNode;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        /** @var Node|null $nextUsageOfVariable */
        if ($nextUsageOfVariable === null) {
            return $this->betterNodeFinder->findFirstNext($expr, function (Node $node) use (
                $assign, $expr
            ): bool {
                if ($this->usedVariableNameAnalyzer->isVariableNamed($node, $expr) && $this->hasInParentExpression($assign, $expr)) {
                    return true;
                }

                if ($node instanceof FuncCall) {
                    return $this->compactFuncCallAnalyzer->isInCompact($node, $expr);
                }

                return $node instanceof Include_;
            });
        }

        return $nextUsageOfVariable;
    }

    private function hasInParentExpression(Assign $assign, Variable $variable): bool
    {
        $name = $this->nodeNameResolver->getName($variable);
        if ($name === null) {
            return false;
        }

        return $this->betterNodeFinder->hasVariableOfName($assign->expr, $name);
    }
}
