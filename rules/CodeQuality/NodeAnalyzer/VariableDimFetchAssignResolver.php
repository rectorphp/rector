<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\CodeQuality\ValueObject\KeyAndExpr;
use Rector\Core\PhpParser\Comparing\NodeComparator;
final class VariableDimFetchAssignResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Stmt[] $stmts
     * @return KeyAndExpr[]
     */
    public function resolveFromStmtsAndVariable(array $stmts, \PhpParser\Node\Expr\Variable $variable) : array
    {
        $keysAndExprs = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                return [];
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof \PhpParser\Node\Expr\Assign) {
                return [];
            }
            $assign = $stmtExpr;
            $keyExpr = $this->matchKeyOnArrayDimFetchOfVariable($assign, $variable);
            if (!$keyExpr instanceof \PhpParser\Node\Expr) {
                return [];
            }
            $keysAndExprs[] = new \Rector\CodeQuality\ValueObject\KeyAndExpr($keyExpr, $assign->expr);
        }
        return $keysAndExprs;
    }
    public function matchKeyOnArrayDimFetchOfVariable(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr
    {
        if (!$assign->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $assign->var;
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->var, $variable)) {
            return null;
        }
        return $arrayDimFetch->dim;
    }
}
