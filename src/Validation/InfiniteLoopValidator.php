<?php

declare(strict_types=1);

namespace Rector\Core\Validation;

use PhpParser\Node;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class InfiniteLoopValidator
{
    public function __construct(
        private readonly NodeComparator $nodeComparator,
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @param Node|array<Node> $node
     */
    public function isValid(Node|array $node, Node $originalNode, string $rectorClass): bool
    {
        if ($this->nodeComparator->areNodesEqual($node, $originalNode)) {
            return true;
        }

        $isFound = (bool) $this->betterNodeFinder->findFirst(
            $node,
            fn (Node $subNode): bool => $this->nodeComparator->areNodesEqual($node, $subNode)
        );

        if (! $isFound) {
            return true;
        }

        $createdByRule = $originalNode->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        if ($createdByRule === []) {
            return true;
        }

        return ! in_array($rectorClass, $createdByRule, true);
    }
}
