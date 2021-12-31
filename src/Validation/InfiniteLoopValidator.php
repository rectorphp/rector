<?php

declare (strict_types=1);
namespace Rector\Core\Validation;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\NodeTraverser\InfiniteLoopTraversingException;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeVisitor\CreatedByRuleNodeVisitor;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
final class InfiniteLoopValidator
{
    /**
     * @var array<class-string<RectorInterface>>
     */
    private const ALLOWED_INFINITE_RECTOR_CLASSES = [\Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector::class, \Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector::class, \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeDecorator\CreatedByRuleDecorator
     */
    private $createdByRuleDecorator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeDecorator\CreatedByRuleDecorator $createdByRuleDecorator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->createdByRuleDecorator = $createdByRuleDecorator;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function process(\PhpParser\Node $node, \PhpParser\Node $originalNode, string $rectorClass) : void
    {
        if (\in_array($rectorClass, self::ALLOWED_INFINITE_RECTOR_CLASSES, \true)) {
            return;
        }
        $createdByRule = $originalNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [];
        // special case
        if (\in_array($rectorClass, $createdByRule, \true)) {
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
        $createdByRuleNodeVisitor = new \Rector\Core\PhpParser\NodeVisitor\CreatedByRuleNodeVisitor($this->createdByRuleDecorator, $rectorClass);
        $nodeTraverser->addVisitor($createdByRuleNodeVisitor);
        $nodeTraverser->traverse([$node]);
    }
}
