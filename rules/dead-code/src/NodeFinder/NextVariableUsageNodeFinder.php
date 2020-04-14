<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NextVariableUsageNodeFinder
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ParentScopeFinder
     */
    private $parentScopeFinder;

    public function __construct(
        ParentScopeFinder $parentScopeFinder,
        CallableNodeTraverser $callableNodeTraverser,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parentScopeFinder = $parentScopeFinder;
    }

    public function find(Assign $assign): ?Node
    {
        $scopeNode = $this->parentScopeFinder->find($assign);
        if ($scopeNode === null) {
            return null;
        }

        $expr = $assign->var;

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $scopeNode->stmts, function (Node $currentNode) use (
            $expr,
            &$nextUsageOfVariable
        ) {
            // used above the assign
            if ($currentNode->getStartTokenPos() < $expr->getStartTokenPos()) {
                return null;
            }

            // skip self
            if ($currentNode === $expr) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($currentNode, $expr)) {
                return null;
            }

            $currentNodeParent = $currentNode->getAttribute(AttributeKey::PARENT_NODE);

            // stop at next assign
            if ($currentNodeParent instanceof Assign) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $nextUsageOfVariable = $currentNode;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $nextUsageOfVariable;
    }
}
