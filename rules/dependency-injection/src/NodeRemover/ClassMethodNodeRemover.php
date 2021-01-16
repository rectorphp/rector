<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeRemover;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodNodeRemover
{
    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        NodesToRemoveCollector $nodesToRemoveCollector
    ) {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    public function removeClassMethodIfUseless(ClassMethod $classMethod): void
    {
        if ($classMethod->params !== []) {
            return;
        }

        if ((array) $classMethod->stmts !== []) {
            return;
        }

        $this->nodesToRemoveCollector->addNodeToRemove($classMethod);
    }

    public function removeParamFromMethodBody(ClassMethod $classMethod, Param $param): void
    {
        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param->var);

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($paramName) {
                if (! $this->isParentConstructStaticCall($node)) {
                    return null;
                }

                /** @var StaticCall $node */
                $this->removeParamFromArgs($node, $paramName);

                if ($node->args === []) {
                    $this->nodesToRemoveCollector->addNodeToRemove($node);
                }

                return null;
            }
        );

        foreach ((array) $classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if (! $this->isParentConstructStaticCall($stmt)) {
                continue;
            }

            /** @var StaticCall $stmt */
            if ($stmt->args !== []) {
                continue;
            }

            unset($classMethod->stmts[$key]);
        }

        $this->removeParamFromAssign($classMethod, $paramName);
    }

    private function isParentConstructStaticCall(Node $node): bool
    {
        return $this->isStaticCallNamed($node, 'parent', MethodName::CONSTRUCT);
    }

    private function removeParamFromArgs(StaticCall $staticCall, string $paramName): void
    {
        foreach ($staticCall->args as $key => $arg) {
            if (! $this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }

            unset($staticCall->args[$key]);
        }
    }

    private function removeParamFromAssign(ClassMethod $classMethod, string $paramName): void
    {
        foreach ((array) $classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if (! $stmt instanceof Assign) {
                continue;
            }

            if (! $stmt->expr instanceof Variable) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($stmt->expr, $paramName)) {
                continue;
            }

            unset($classMethod->stmts[$key]);
        }
    }

    private function isStaticCallNamed(Node $node, string $class, string $method): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->class, $class)) {
            return false;
        }

        return $this->nodeNameResolver->isName($node->name, $method);
    }
}
