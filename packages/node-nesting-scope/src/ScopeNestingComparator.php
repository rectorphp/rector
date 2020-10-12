<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;

final class ScopeNestingComparator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function areScopeNestingEqual(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeScopeNode = $this->findParentControlStructure($firstNode);
        $secondNodeScopeNode = $this->findParentControlStructure($secondNode);

        return $firstNodeScopeNode === $secondNodeScopeNode;
    }

    public function isNodeConditionallyScoped(Node $node): bool
    {
        $foundParentType = $this->betterNodeFinder->findFirstParentInstanceOf(
            $node,
            ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [FunctionLike::class]
        );

        if ($foundParentType === null) {
            return false;
        }

        return ! $foundParentType instanceof FunctionLike;
    }

    private function findParentControlStructure(Node $node): ?Node
    {
        return $this->betterNodeFinder->findFirstParentInstanceOf($node, ControlStructure::BREAKING_SCOPE_NODE_TYPES);
    }
}
