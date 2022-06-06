<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\CodeQuality\ValueObject\DefaultPropertyExprAssign;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ExprAnalyzer;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
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
                continue;
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
