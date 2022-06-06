<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\CodeQuality\ValueObject\DefaultPropertyExprAssign;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class ConstructorPropertyDefaultExprResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\ExprAnalyzer $exprAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    /**
     * @return DefaultPropertyExprAssign[]
     */
    public function resolve(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $stmts = $classMethod->getStmts();
        if ($stmts === null) {
            return [];
        }
        $defaultPropertyExprAssigns = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $nestedStmt = $stmt->expr;
            if (!$nestedStmt instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            $assign = $nestedStmt;
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }
            $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
            if (!\is_string($propertyName)) {
                continue;
            }
            $expr = $assign->expr;
            if ($this->exprAnalyzer->isDynamicExpr($expr)) {
                continue;
            }
            $defaultPropertyExprAssigns[] = new \Rector\CodeQuality\ValueObject\DefaultPropertyExprAssign($stmt, $propertyName, $expr);
        }
        return $defaultPropertyExprAssigns;
    }
}
