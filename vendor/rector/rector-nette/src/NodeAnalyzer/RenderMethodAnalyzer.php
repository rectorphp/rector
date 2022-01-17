<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class RenderMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return MethodCall[]
     */
    public function machRenderMethodCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        /** @var MethodCall[] $methodsCalls */
        $methodsCalls = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\MethodCall::class);
        $renderMethodCalls = [];
        foreach ($methodsCalls as $methodCall) {
            if ($this->nodeNameResolver->isName($methodCall->name, 'render')) {
                $renderMethodCalls[] = $methodCall;
            }
        }
        return $renderMethodCalls;
    }
}
