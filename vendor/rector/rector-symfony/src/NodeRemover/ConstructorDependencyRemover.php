<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeRemover;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
final class ConstructorDependencyRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $paramNames
     */
    public function removeParamsByName(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $paramNames) : \PhpParser\Node\Stmt\ClassMethod
    {
        $this->removeParams($classMethod, $paramNames);
        return $this->removeAssigns($classMethod, $paramNames);
    }
    /**
     * @param string[] $paramNames
     */
    private function removeParams(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $paramNames) : void
    {
        foreach ($classMethod->params as $key => $param) {
            if (!$this->nodeNameResolver->isNames($param, $paramNames)) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
    }
    /**
     * @param string[] $paramNames
     */
    private function removeAssigns(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $paramNames) : \PhpParser\Node\Stmt\ClassMethod
    {
        // remove assign
        foreach ((array) $classMethod->stmts as $stmtKey => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($assign->expr, $paramNames)) {
                continue;
            }
            unset($classMethod->stmts[$stmtKey]);
        }
        return $classMethod;
    }
}
