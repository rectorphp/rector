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
use PhpParser\Node\Stmt\Class_;
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
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ClassReflection;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer;
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
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer
     */
    private $neverFuncCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, NeverFuncCallAnalyzer $neverFuncCallAnalyzer, ValueResolver $valueResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->neverFuncCallAnalyzer = $neverFuncCallAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
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
        return !(bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($functionLike, function (Node $subNode) : bool {
            if ($subNode instanceof Yield_ || $subNode instanceof YieldFrom) {
                return \true;
            }
            return $subNode instanceof Return_ && $subNode->expr instanceof Expr;
        });
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
            if ($this->neverFuncCallAnalyzer->isWithNeverTypeExpr($stmt)) {
                return \true;
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
            if (!$this->isDoOrWhileWithAlwaysReturnOrExit($stmt)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\While_ $node
     */
    private function isFoundLoopControl($node) : bool
    {
        $isFoundLoopControl = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node->stmts, static function (Node $subNode) use(&$isFoundLoopControl) {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof Break_ || $subNode instanceof Continue_ || $subNode instanceof Goto_) {
                $isFoundLoopControl = \true;
                return NodeTraverser::STOP_TRAVERSAL;
            }
        });
        return $isFoundLoopControl;
    }
    private function isDoOrWhileWithAlwaysReturnOrExit(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Do_ && !$stmt instanceof While_) {
            return \false;
        }
        if ($this->valueResolver->isTrue($stmt->cond)) {
            return !$this->isFoundLoopControl($stmt);
        }
        if (!$this->hasStmtsAlwaysReturnOrExit($stmt->stmts)) {
            return \false;
        }
        return $stmt instanceof Do_ && !$this->isFoundLoopControl($stmt);
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
    private function isStopped(Stmt $stmt) : bool
    {
        if ($stmt instanceof Expression) {
            $stmt = $stmt->expr;
        }
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
        $hasReturnOrExitInFinally = $tryCatch->finally instanceof Finally_ && $this->hasStmtsAlwaysReturnOrExit($tryCatch->finally->stmts);
        if (!$this->hasStmtsAlwaysReturnOrExit($tryCatch->stmts)) {
            return $hasReturnOrExitInFinally;
        }
        foreach ($tryCatch->catches as $catch) {
            if ($this->hasStmtsAlwaysReturnOrExit($catch->stmts)) {
                continue;
            }
            if ($hasReturnOrExitInFinally) {
                continue;
            }
            return \false;
        }
        return \true;
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
