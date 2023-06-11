<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\CodeQuality\ValueObject\KeyAndExpr;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class VariableDimFetchAssignResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeComparator $nodeComparator, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Stmt[] $stmts
     * @return KeyAndExpr[]
     */
    public function resolveFromStmtsAndVariable(array $stmts, Variable $variable) : array
    {
        $keysAndExprs = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                return [];
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof Assign) {
                return [];
            }
            $assign = $stmtExpr;
            $keyExpr = $this->matchKeyOnArrayDimFetchOfVariable($assign, $variable);
            $keysAndExprs[] = new KeyAndExpr($keyExpr, $assign->expr, $stmt->getComments());
        }
        // we can only work with same variable
        // and exclusively various keys or empty keys
        if (!$this->hasExclusivelyNullKeyOrFilledKey($keysAndExprs)) {
            return [];
        }
        return $keysAndExprs;
    }
    private function matchKeyOnArrayDimFetchOfVariable(Assign $assign, Variable $variable) : ?Expr
    {
        if (!$assign->var instanceof ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $assign->var;
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->var, $variable)) {
            return null;
        }
        $isFoundInExpr = (bool) $this->betterNodeFinder->findFirst($assign->expr, function (Node $subNode) use($variable) : bool {
            return $this->nodeComparator->areNodesEqual($subNode, $variable);
        });
        if ($isFoundInExpr) {
            return null;
        }
        return $arrayDimFetch->dim;
    }
    /**
     * @param KeyAndExpr[] $keysAndExprs
     */
    private function hasExclusivelyNullKeyOrFilledKey(array $keysAndExprs) : bool
    {
        $alwaysNullKey = \true;
        $alwaysStringKey = \true;
        foreach ($keysAndExprs as $keyAndExpr) {
            if ($keyAndExpr->getKeyExpr() instanceof Expr) {
                $alwaysNullKey = \false;
            } else {
                $alwaysStringKey = \false;
            }
        }
        if ($alwaysNullKey) {
            return \true;
        }
        return $alwaysStringKey;
    }
}
