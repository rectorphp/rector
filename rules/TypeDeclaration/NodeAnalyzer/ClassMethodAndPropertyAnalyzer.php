<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodAndPropertyAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasPropertyFetchReturn(ClassMethod $classMethod, string $propertyName): bool
    {
        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 1) {
            return \false;
        }
        $onlyClassMethodStmt = $stmts[0] ?? null;
        if (!$onlyClassMethodStmt instanceof Return_) {
            return \false;
        }
        /** @var Return_ $return */
        $return = $onlyClassMethodStmt;
        if (!$return->expr instanceof PropertyFetch) {
            return \false;
        }
        return $this->nodeNameResolver->isName($return->expr, $propertyName);
    }
    public function hasOnlyPropertyAssign(ClassMethod $classMethod, string $propertyName): bool
    {
        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 1) {
            return \false;
        }
        $onlyClassMethodStmt = $stmts[0];
        return $this->isLocalPropertyVariableAssign($onlyClassMethodStmt, $propertyName);
    }
    public function hasPropertyAssignWithReturnThis(ClassMethod $classMethod): bool
    {
        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 2) {
            return \false;
        }
        $possibleAssignStmt = $stmts[0];
        $possibleReturnThis = $stmts[1];
        if (!$this->isLocalPropertyVariableAssign($possibleAssignStmt, null)) {
            return \false;
        }
        if (!$possibleReturnThis instanceof Return_) {
            return \false;
        }
        $returnExpr = $possibleReturnThis->expr;
        if (!$returnExpr instanceof Variable) {
            return \false;
        }
        return $this->nodeNameResolver->isName($returnExpr, 'this');
    }
    private function isLocalPropertyVariableAssign(Stmt $onlyClassMethodStmt, ?string $propertyName): bool
    {
        if (!$onlyClassMethodStmt instanceof Expression) {
            return \false;
        }
        if (!$onlyClassMethodStmt->expr instanceof Assign) {
            return \false;
        }
        $assign = $onlyClassMethodStmt->expr;
        $assignVar = $assign->var;
        if (!$assignVar instanceof PropertyFetch) {
            return \false;
        }
        $propertyFetch = $assignVar;
        if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
            return \false;
        }
        if ($propertyName) {
            return $this->nodeNameResolver->isName($propertyFetch->name, $propertyName);
        }
        return \true;
    }
}
