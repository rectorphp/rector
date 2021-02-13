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

    public function machRenderMethodCall(ClassMethod $classMethod): ?MethodCall
    {
        /** @var MethodCall[] $methodsCalls */
        $methodsCalls = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, MethodCall::class);

        foreach ($methodsCalls as $methodCall) {
            if ($this->nodeNameResolver->isName($methodCall->name, 'render')) {
                return $methodCall;
            }
        }

        return null;
    }
}
