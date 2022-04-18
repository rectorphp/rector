<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220418\Symplify\PackageBuilder\Parameter\ParameterProvider;
/**
 * Skips performance trap in PHPStan: https://github.com/phpstan/phpstan/issues/254
 */
final class RemoveDeepChainMethodCallNodeVisitor extends \PhpParser\NodeVisitorAbstract
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \RectorPrefix20220418\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nestedChainMethodCallLimit = (int) $parameterProvider->provideParameter(\Rector\Core\Configuration\Option::NESTED_CHAIN_METHOD_CALL_LIMIT);
    }
    public function enterNode(\PhpParser\Node $node) : ?int
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\MethodCall && $node->expr->var instanceof \PhpParser\Node\Expr\MethodCall) {
            $nestedChainMethodCalls = $this->betterNodeFinder->findInstanceOf([$node->expr], \PhpParser\Node\Expr\MethodCall::class);
            if (\count($nestedChainMethodCalls) > $this->nestedChainMethodCallLimit) {
                $this->removingExpression = $node;
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }
        return null;
    }
    /**
     * @return \PhpParser\Node|\PhpParser\Node\Stmt\Nop
     */
    public function leaveNode(\PhpParser\Node $node)
    {
        if ($node === $this->removingExpression) {
            // keep any node, so we don't remove it permanently
            $nop = new \PhpParser\Node\Stmt\Nop();
            $nop->setAttributes($node->getAttributes());
            return $nop;
        }
        return $node;
    }
}
