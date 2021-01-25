<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ParentScopeFinder $parentScopeFinder
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parentScopeFinder = $parentScopeFinder;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
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
        ) use ($expr, &$nextUsageOfVariable): ?int {
            // used above the assign
            if ($currentNode->getStartTokenPos() < $expr->getStartTokenPos()) {
                return null;
            }

            // skip self
            if ($this->betterStandardPrinter->areSameNode($currentNode, $expr)) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($currentNode, $expr)) {
                return null;
            }

            $currentNodeParent = $currentNode->getAttribute(AttributeKey::PARENT_NODE);

            if ($currentNodeParent instanceof Assign && ! $this->hasInParentExpression($currentNodeParent, $expr)) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $nextUsageOfVariable = $currentNode;

            return NodeTraverser::STOP_TRAVERSAL;
        });

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
