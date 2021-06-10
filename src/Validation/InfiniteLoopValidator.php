<?php

declare(strict_types=1);

namespace Rector\Core\Validation;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\NodeTraverser\InfiniteLoopTraversingException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeVisitor\CreatedByRuleNodeVisitor;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class InfiniteLoopValidator
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function process(Node $node, Node $originalNode, string $rectorClass): void
    {
        if ($rectorClass === DowngradeNullsafeToTernaryOperatorRector::class) {
            return;
        }

        $createdByRule = $originalNode->getAttribute(AttributeKey::CREATED_BY_RULE);

        // special case
        if ($createdByRule === $rectorClass) {
            // does it contain the same node type as input?
            $originalNodeClass = $originalNode::class;

            $hasNestedOriginalNodeType = $this->betterNodeFinder->findInstanceOf($node, $originalNodeClass);
            if ($hasNestedOriginalNodeType !== []) {
                throw new InfiniteLoopTraversingException($rectorClass);
            }
        }

        $this->decorateNode($originalNode, $rectorClass);
    }

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function decorateNode(Node $node, string $rectorClass): void
    {
        $nodeTraverser = new NodeTraverser();

        $createdByRuleNodeVisitor = new CreatedByRuleNodeVisitor($rectorClass);
        $nodeTraverser->addVisitor($createdByRuleNodeVisitor);

        $nodeTraverser->traverse([$node]);
    }
}
