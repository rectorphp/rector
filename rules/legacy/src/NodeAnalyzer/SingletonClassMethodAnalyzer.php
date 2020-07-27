<?php

declare(strict_types=1);

namespace Rector\Legacy\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\Manipulator\ConstFetchManipulator;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SingletonClassMethodAnalyzer
{
    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ConstFetchManipulator $constFetchManipulator,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->constFetchManipulator = $constFetchManipulator;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeTypeResolver = $nodeTypeResolver;
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
    public function matchStaticPropertyFetch(ClassMethod $classMethod): ?StaticPropertyFetch
    {
        if (count((array) $classMethod->stmts) !== 2) {
            return null;
        }

        if (! $classMethod->stmts[0] instanceof If_) {
            return null;
        }

        /** @var If_ $if */
        $if = $classMethod->stmts[0];
        $staticPropertyFetch = $this->matchStaticPropertyFetchInIfCond($if->cond);

        if (count($if->stmts) !== 1) {
            return null;
        }

        if (! $if->stmts[0] instanceof Expression) {
            return null;
        }

        $stmt = $if->stmts[0]->expr;

        // create self and assign to static property
        if (! $stmt instanceof Assign) {
            return null;
        }

        if (! $this->betterStandardPrinter->areNodesEqual($staticPropertyFetch, $stmt->var)) {
            return null;
        }

        if (! $stmt->expr instanceof New_) {
            return null;
        }

        /** @var string $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        // the "self" class is created
        if (! $this->nodeTypeResolver->isObjectType($stmt->expr->class, $class)) {
            return null;
        }

        /** @var StaticPropertyFetch $staticPropertyFetch */
        return $staticPropertyFetch;
    }

    private function matchStaticPropertyFetchInIfCond(Expr $expr): ?StaticPropertyFetch
    {
        // matching: "self::$static === null"
        if ($expr instanceof Identical) {
            if ($this->constFetchManipulator->isNull($expr->left) && $expr->right instanceof StaticPropertyFetch) {
                return $expr->right;
            }

            if ($this->constFetchManipulator->isNull($expr->right) && $expr->left instanceof StaticPropertyFetch) {
                return $expr->left;
            }
        }

        // matching: "! self::$static"
        if ($expr instanceof BooleanNot && $expr->expr instanceof StaticPropertyFetch) {
            return $expr->expr;
        }

        return null;
    }
}
