<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function process(\PhpParser\Node $node, \PhpParser\Node $originalNode, string $rectorClass) : void
    {
        if ($rectorClass === \Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector::class) {
            return;
        }
        $createdByRule = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE);
        // special case
        if ($createdByRule === $rectorClass) {
            // does it contain the same node type as input?
            $originalNodeClass = \get_class($originalNode);
            $hasNestedOriginalNodeType = $this->betterNodeFinder->findInstanceOf($node, $originalNodeClass);
            if ($hasNestedOriginalNodeType !== []) {
                throw new \Rector\Core\Exception\NodeTraverser\InfiniteLoopTraversingException($rectorClass);
            }
        }
        $this->decorateNode($originalNode, $rectorClass);
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function decorateNode(\PhpParser\Node $node, string $rectorClass) : void
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $createdByRuleNodeVisitor = new \Rector\Core\PhpParser\NodeVisitor\CreatedByRuleNodeVisitor($rectorClass);
        $nodeTraverser->addVisitor($createdByRuleNodeVisitor);
        $nodeTraverser->traverse([$node]);
    }
}
