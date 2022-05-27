<?php

declare (strict_types=1);
namespace Rector\Transform\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class SingletonClassMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Match this code:
     * if (null === static::$instance) {
     *     static::$instance = new static();
     * }
     * return static::$instance;
     *
     * Matches "static::$instance" on success
     */
    public function matchStaticPropertyFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr\StaticPropertyFetch
    {
        $stmts = (array) $classMethod->stmts;
        if (\count($stmts) !== 2) {
            return null;
        }
        $firstStmt = $stmts[0] ?? null;
        if (!$firstStmt instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        $staticPropertyFetch = $this->matchStaticPropertyFetchInIfCond($firstStmt->cond);
        if (\count($firstStmt->stmts) !== 1) {
            return null;
        }
        if (!$firstStmt->stmts[0] instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmt = $firstStmt->stmts[0]->expr;
        // create self and assign to static property
        if (!$stmt instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($staticPropertyFetch, $stmt->var)) {
            return null;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return null;
        }
        // the "self" class is created
        if (!$this->nodeTypeResolver->isObjectType($stmt->expr->class, new \PHPStan\Type\ObjectType($className))) {
            return null;
        }
        return $staticPropertyFetch;
    }
    private function matchStaticPropertyFetchInIfCond(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\StaticPropertyFetch
    {
        // matching: "self::$static === null"
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            if ($this->valueResolver->isNull($expr->left) && $expr->right instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return $expr->right;
            }
            if ($this->valueResolver->isNull($expr->right) && $expr->left instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return $expr->left;
            }
        }
        // matching: "! self::$static"
        if (!$expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            return null;
        }
        $negatedExpr = $expr->expr;
        if (!$negatedExpr instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return null;
        }
        return $negatedExpr;
    }
}
