<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\PhpParser\NodeVisitorAbstract;
use RectorPrefix20220606\Rector\Core\Configuration\Option;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Skips performance trap in PHPStan: https://github.com/phpstan/phpstan/issues/254
 */
final class RemoveDeepChainMethodCallNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @readonly
     * @var int
     */
    private $nestedChainMethodCallLimit;
    /**
     * @var \PhpParser\Node\Stmt\Expression|null
     */
    private $removingExpression;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder, ParameterProvider $parameterProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nestedChainMethodCallLimit = (int) $parameterProvider->provideParameter(Option::NESTED_CHAIN_METHOD_CALL_LIMIT);
    }
    public function enterNode(Node $node) : ?int
    {
        if (!$node instanceof Expression) {
            return null;
        }
        if ($node->expr instanceof MethodCall && $node->expr->var instanceof MethodCall) {
            $nestedChainMethodCalls = $this->betterNodeFinder->findInstanceOf([$node->expr], MethodCall::class);
            if (\count($nestedChainMethodCalls) > $this->nestedChainMethodCallLimit) {
                $this->removingExpression = $node;
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Stmt\Nop|\PhpParser\Node
     */
    public function leaveNode(Node $node)
    {
        if ($node === $this->removingExpression) {
            // keep any node, so we don't remove it permanently
            $nop = new Nop();
            $nop->setAttributes($node->getAttributes());
            return $nop;
        }
        return $node;
    }
}
