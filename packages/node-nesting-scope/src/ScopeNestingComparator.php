<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNestingScope\ValueObject\ControlStructure;

final class ScopeNestingComparator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterNodeFinder $betterNodeFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function areScopeNestingEqual(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeScopeNode = $this->findParentControlStructure($firstNode);
        $secondNodeScopeNode = $this->findParentControlStructure($secondNode);

        return $this->betterStandardPrinter->areNodesEqual($firstNodeScopeNode, $secondNodeScopeNode);
    }

    public function isNodeConditionallyScoped(Node $node): bool
    {
        $foundParentType = $this->betterNodeFinder->findParentTypes(
            $node,
            ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [FunctionLike::class]
        );

        if (! $foundParentType instanceof Node) {
            return false;
        }

        return ! $foundParentType instanceof FunctionLike;
    }

    private function findParentControlStructure(Node $node): ?Node
    {
        return $this->betterNodeFinder->findParentTypes($node, ControlStructure::BREAKING_SCOPE_NODE_TYPES);
    }
}
