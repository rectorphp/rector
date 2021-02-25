<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class RenderMethodAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return MethodCall[]
     */
    public function machRenderMethodCalls(ClassMethod $classMethod): array
    {
        /** @var MethodCall[] $methodsCalls */
        $methodsCalls = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, MethodCall::class);

        $renderMethodCalls = [];
        foreach ($methodsCalls as $methodsCall) {
            if ($this->nodeNameResolver->isName($methodsCall->name, 'render')) {
                $renderMethodCalls[] = $methodsCall;
            }
        }

        return $renderMethodCalls;
    }
}
