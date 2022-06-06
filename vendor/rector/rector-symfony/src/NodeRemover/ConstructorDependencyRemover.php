<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeRemover;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ConstructorDependencyRemover
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param string[] $paramNames
     */
    public function removeParamsByName(ClassMethod $classMethod, array $paramNames) : ClassMethod
    {
        $this->removeParams($classMethod, $paramNames);
        return $this->removeAssigns($classMethod, $paramNames);
    }
    /**
     * @param string[] $paramNames
     */
    private function removeParams(ClassMethod $classMethod, array $paramNames) : void
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
    private function removeAssigns(ClassMethod $classMethod, array $paramNames) : ClassMethod
    {
        // remove assign
        foreach ((array) $classMethod->stmts as $stmtKey => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof Variable) {
                continue;
            }
            if (!$assign->var instanceof PropertyFetch) {
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
