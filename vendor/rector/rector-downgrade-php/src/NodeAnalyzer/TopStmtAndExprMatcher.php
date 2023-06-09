<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ValueObject\StmtAndExpr;
/**
 * To resolve Stmt and Expr in top stmtInterface from early Expr attribute
 * so the usage can append code before the Stmt
 */
final class TopStmtAndExprMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(BetterNodeFinder $betterNodeFinder, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    public function match($stmt, callable $filter) : ?\Rector\ValueObject\StmtAndExpr
    {
        if ($stmt instanceof Closure) {
            return null;
        }
        $nodes = [];
        if ($stmt instanceof Foreach_) {
            // keyVar can be null, so need to be filtered only Expr
            $nodes = \array_filter([$stmt->expr, $stmt->keyVar, $stmt->valueVar]);
        }
        if ($stmt instanceof For_) {
            $nodes = \array_merge($stmt->init, $stmt->cond, $stmt->loop);
        }
        if ($this->multiInstanceofChecker->isInstanceOf($stmt, [If_::class, While_::class, Do_::class, Switch_::class])) {
            /** @var If_|While_|Do_|Switch_ $stmt */
            $nodes = [$stmt->cond];
        }
        if ($stmt instanceof Echo_) {
            $nodes = $stmt->exprs;
        }
        $expr = $this->resolveExpr($stmt, $nodes, $filter);
        if ($expr instanceof Expr) {
            return new StmtAndExpr($stmt, $expr);
        }
        $stmtAndExpr = $this->resolveFromChildCond($stmt, $filter);
        if ($stmtAndExpr instanceof StmtAndExpr) {
            return $stmtAndExpr;
        }
        return $this->resolveOnReturnOrExpression($stmt, $filter);
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function resolveOnReturnOrExpression($stmt, callable $filter) : ?StmtAndExpr
    {
        if (!$stmt instanceof Return_ && !$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Expr) {
            return null;
        }
        $expr = $this->resolveExpr($stmt, $stmt->expr, $filter);
        if ($expr instanceof Expr) {
            return new StmtAndExpr($stmt, $expr);
        }
        return null;
    }
    /**
     * @param mixed[]|\PhpParser\Node\Expr $exprs
     * @param callable(Node $node): bool $filter
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function resolveExpr($stmt, $exprs, callable $filter) : ?Expr
    {
        $node = $this->betterNodeFinder->findFirst($exprs, $filter);
        if (!$node instanceof Expr) {
            return null;
        }
        $stmtScope = $stmt->getAttribute(AttributeKey::SCOPE);
        $exprScope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$stmtScope instanceof Scope || !$exprScope instanceof Scope) {
            return null;
        }
        if ($stmtScope->getParentScope() === $exprScope->getParentScope()) {
            return $node;
        }
        return null;
    }
    /**
     * @param callable(Node $node): bool $filter
     * @param \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\Return_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Echo_ $stmt
     */
    private function resolveFromChildCond($stmt, callable $filter) : ?\Rector\ValueObject\StmtAndExpr
    {
        if (!$stmt instanceof If_ && !$stmt instanceof Switch_) {
            return null;
        }
        $stmts = $stmt instanceof If_ ? $stmt->elseifs : $stmt->cases;
        foreach ($stmts as $stmt) {
            if (!$stmt->cond instanceof Expr) {
                continue;
            }
            $expr = $this->resolveExpr($stmt, $stmt->cond, $filter);
            if ($expr instanceof Expr) {
                return new StmtAndExpr($stmt, $expr);
            }
        }
        return null;
    }
}
