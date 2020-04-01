<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope\NodeFinder;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNestingScope\ValueObject\ControlStructure;

final class ScopeAwareNodeFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var bool
     */
    private $isBreakingNodeFoundFirst = false;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * Find node based on $callable or null, when the nesting scope is broken
     * @param class-string[] $allowedTypes
     */
    public function findParent(Node $node, callable $callable, array $allowedTypes): ?Node
    {
        $foundNode = null;

        $parentNestingBreakTypes = array_diff(ControlStructure::NODE_TYPES, $allowedTypes);

        $this->isBreakingNodeFoundFirst = false;
        $foundNode = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use (
            $callable,
            $parentNestingBreakTypes
        ) {
            if ($callable($node)) {
                return true;
            }

            foreach ($parentNestingBreakTypes as $parentNestingBreakType) {
                if (is_a($node, $parentNestingBreakType, true)) {
                    $this->isBreakingNodeFoundFirst = true;
                    return true;
                }
            }

            return false;
        });

        if ($this->isBreakingNodeFoundFirst) {
            return null;
        }

        return $foundNode;
    }
}
