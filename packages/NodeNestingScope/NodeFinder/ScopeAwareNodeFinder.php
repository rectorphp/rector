<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope\NodeFinder;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
final class ScopeAwareNodeFinder
{
    /**
     * @var bool
     */
    private $isBreakingNodeFoundFirst = \false;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @api
     * Find node based on $callable or null, when the nesting scope is broken
     * @param callable(Node $node): bool $callable
     * @param array<class-string<Node>> $allowedTypes
     */
    public function findParent(Node $node, callable $callable, array $allowedTypes) : ?Node
    {
        /** @var array<class-string<Node>> $parentNestingBreakTypes */
        $parentNestingBreakTypes = \array_diff(ControlStructure::BREAKING_SCOPE_NODE_TYPES, $allowedTypes);
        $this->isBreakingNodeFoundFirst = \false;
        $foundNode = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use($callable, $parentNestingBreakTypes) : bool {
            if ($callable($node)) {
                return \true;
            }
            foreach ($parentNestingBreakTypes as $parentNestingBreakType) {
                if (!$node instanceof $parentNestingBreakType) {
                    continue;
                }
                $this->isBreakingNodeFoundFirst = \true;
                return \true;
            }
            return \false;
        });
        if ($this->isBreakingNodeFoundFirst) {
            return null;
        }
        return $foundNode;
    }
}
