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
    public function __construct(NodeNameResolver $nodeNameResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    /**
     * @return DefaultPropertyExprAssign[]
     */
    public function resolve(ClassMethod $classMethod) : array
    {
        $stmts = $classMethod->getStmts();
        if ($stmts === null) {
            return [];
        }
        $defaultPropertyExprAssigns = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                break;
            }
            $nestedStmt = $stmt->expr;
            if (!$nestedStmt instanceof Assign) {
                continue;
            }
            $assign = $nestedStmt;
            if (!$assign->var instanceof PropertyFetch) {
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
            $defaultPropertyExprAssigns[] = new DefaultPropertyExprAssign($stmt, $propertyName, $expr);
        }
        return $defaultPropertyExprAssigns;
    }
}
