<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\NodeRemover;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodNodeRemover
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }
    public function removeClassMethodIfUseless(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($classMethod->params !== []) {
            return;
        }
        if ((array) $classMethod->stmts !== []) {
            return;
        }
        $this->nodesToRemoveCollector->addNodeToRemove($classMethod);
    }
    public function removeParamFromMethodBody(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : void
    {
        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param->var);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($paramName) {
            if (!$this->isParentConstructStaticCall($node)) {
                return null;
            }
            /** @var StaticCall $node */
            $this->removeParamFromArgs($node, $paramName);
            if ($node->args === []) {
                $this->nodesToRemoveCollector->addNodeToRemove($node);
            }
            return null;
        });
        foreach ((array) $classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $stmt = $stmt->expr;
            }
            if (!$this->isParentConstructStaticCall($stmt)) {
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
    private function isParentConstructStaticCall(\PhpParser\Node $node) : bool
    {
        return $this->isStaticCallNamed($node, 'parent', \Rector\Core\ValueObject\MethodName::CONSTRUCT);
    }
    private function removeParamFromArgs(\PhpParser\Node\Expr\StaticCall $staticCall, string $paramName) : void
    {
        foreach ($staticCall->args as $key => $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }
            unset($staticCall->args[$key]);
        }
    }
    private function removeParamFromAssign(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $paramName) : void
    {
        foreach ((array) $classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $stmt = $stmt->expr;
            }
            if (!$stmt instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($stmt->expr, $paramName)) {
                continue;
            }
            unset($classMethod->stmts[$key]);
        }
    }
    private function isStaticCallNamed(\PhpParser\Node $node, string $class, string $method) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->class, $class)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, $method);
    }
}
