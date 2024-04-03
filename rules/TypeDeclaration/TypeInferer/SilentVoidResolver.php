<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Reflection\ClassReflection;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ReflectionResolver;
final class SilentVoidResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasExclusiveVoid($functionLike) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if ($classReflection instanceof ClassReflection && $classReflection->isInterface()) {
            return \false;
        }
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($functionLike, [Yield_::class, YieldFrom::class])) {
            return \false;
        }
        $return = $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, static function (Node $node) : bool {
            return $node instanceof Return_ && $node->expr instanceof Expr;
        });
        return !$return instanceof Return_;
    }
    public function hasSilentVoid(FunctionLike $functionLike) : bool
    {
        if ($functionLike instanceof ArrowFunction) {
            return \false;
        }
        $stmts = (array) $functionLike->getStmts();
        return !$this->hasStmtsAlwaysReturnOrExit($stmts);
    }
    /**
     * @param Stmt[]|Expression[] $stmts
     */
    private function hasStmtsAlwaysReturnOrExit(array $stmts) : bool
    {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }
            if ($this->isStopped($stmt)) {
                return \true;
            }
            // has switch with always return
            if ($stmt instanceof Switch_ && $this->isSwitchWithAlwaysReturnOrExit($stmt)) {
                return \true;
            }
            if ($stmt instanceof TryCatch && $this->isTryCatchAlwaysReturnOrExit($stmt)) {
                return \true;
            }
            if ($this->isIfReturn($stmt)) {
                return \true;
            }
            if ($stmt instanceof Do_ && $this->isDoWithAlwaysReturnOrExit($stmt)) {
                return \true;
            }
        }
        return \false;
    }
    private function isDoWithAlwaysReturnOrExit(Do_ $do) : bool
    {
        if (!$this->hasStmtsAlwaysReturnOrExit($do->stmts)) {
            return \false;
        }
        return !(bool) $this->betterNodeFinder->findFirst($do->stmts, static function (Node $node) : bool {
            return $node instanceof Break_ || $node instanceof Continue_ || $node instanceof Goto_;
        });
    }
    /**
     * @param \PhpParser\Node\Stmt|\PhpParser\Node\Expr $stmt
     */
    private function isIfReturn($stmt) : bool
    {
        if (!$stmt instanceof If_) {
            return \false;
        }
        foreach ($stmt->elseifs as $elseIf) {
            if (!$this->hasStmtsAlwaysReturnOrExit($elseIf->stmts)) {
                return \false;
            }
        }
        if (!$stmt->else instanceof Else_) {
            return \false;
        }
        if (!$this->hasStmtsAlwaysReturnOrExit($stmt->stmts)) {
            return \false;
        }
        return $this->hasStmtsAlwaysReturnOrExit($stmt->else->stmts);
    }
    /**
     * @param \PhpParser\Node\Stmt|\PhpParser\Node\Expr $stmt
     */
    private function isStopped($stmt) : bool
    {
        return $stmt instanceof Throw_ || $stmt instanceof Exit_ || $stmt instanceof Return_ && $stmt->expr instanceof Expr || $stmt instanceof Yield_ || $stmt instanceof YieldFrom;
    }
    private function isSwitchWithAlwaysReturnOrExit(Switch_ $switch) : bool
    {
        $hasDefault = \false;
        foreach ($switch->cases as $case) {
            if (!$case->cond instanceof Expr) {
                $hasDefault = $case->stmts !== [];
                break;
            }
        }
        if (!$hasDefault) {
            return \false;
        }
        $casesWithReturnOrExitCount = $this->resolveReturnOrExitCount($switch);
        $cases = \array_filter($switch->cases, static function (Case_ $case) : bool {
            return $case->stmts !== [];
        });
        // has same amount of first return or exit nodes as switches
        return \count($cases) === $casesWithReturnOrExitCount;
    }
    private function isTryCatchAlwaysReturnOrExit(TryCatch $tryCatch) : bool
    {
        if (!$this->hasStmtsAlwaysReturnOrExit($tryCatch->stmts)) {
            return \false;
        }
        foreach ($tryCatch->catches as $catch) {
            if (!$this->hasStmtsAlwaysReturnOrExit($catch->stmts)) {
                return \false;
            }
        }
        return !($tryCatch->finally instanceof Finally_ && !$this->hasStmtsAlwaysReturnOrExit($tryCatch->finally->stmts));
    }
    private function resolveReturnOrExitCount(Switch_ $switch) : int
    {
        $casesWithReturnCount = 0;
        foreach ($switch->cases as $case) {
            if ($this->hasStmtsAlwaysReturnOrExit($case->stmts)) {
                ++$casesWithReturnCount;
            }
        }
        return $casesWithReturnCount;
    }
}
