<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Label;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitor;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\FileNode;
final class TerminatedNodeAnalyzer
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINABLE_NODES = [Return_::class, Break_::class, Continue_::class];
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINABLE_NODES_BY_ITS_STMTS = [TryCatch::class, If_::class, Switch_::class];
    /**
     * @var array<class-string<Node>>
     */
    private const ALLOWED_CONTINUE_CURRENT_STMTS = [InlineHTML::class, Nop::class];
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param StmtsAware $stmtsAware
     */
    public function isAlwaysTerminated(Node $stmtsAware, Stmt $node, Stmt $currentStmt): bool
    {
        if (in_array(get_class($currentStmt), self::ALLOWED_CONTINUE_CURRENT_STMTS, \true)) {
            return \false;
        }
        if (($stmtsAware instanceof FileNode || $stmtsAware instanceof Namespace_) && ($currentStmt instanceof ClassLike || $currentStmt instanceof Function_)) {
            return \false;
        }
        // an infinite loop with no break never falls through to the next stmt
        if ($node instanceof While_ || $node instanceof Do_ || $node instanceof For_) {
            return $this->isTerminatedInfiniteLoop($node);
        }
        if (!in_array(get_class($node), self::TERMINABLE_NODES_BY_ITS_STMTS, \true)) {
            return $this->isTerminatedNode($node, $currentStmt);
        }
        if ($node instanceof TryCatch) {
            return $this->isTerminatedInLastStmtsTryCatch($node);
        }
        if ($node instanceof If_) {
            return $this->isTerminatedInLastStmtsIf($node);
        }
        /** @var Switch_ $node */
        return $this->isTerminatedInLastStmtsSwitch($node);
    }
    /**
     * @param \PhpParser\Node\Stmt\While_|\PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\For_ $loop
     */
    private function isTerminatedInfiniteLoop($loop): bool
    {
        if (!$this->isInfiniteLoopCondition($loop)) {
            return \false;
        }
        // a break/goto escaping the loop makes the following stmt reachable again;
        // stay conservative and treat any break/goto in the body as escaping
        return !$this->hasBreakOrGoto($loop->stmts);
    }
    /**
     * @param \PhpParser\Node\Stmt\While_|\PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\For_ $loop
     */
    private function isInfiniteLoopCondition($loop): bool
    {
        if ($loop instanceof For_) {
            // "for (;;)" has no condition and loops forever
            if ($loop->cond === []) {
                return \true;
            }
            $lastCond = end($loop->cond);
            return $lastCond instanceof Expr && $this->isAlwaysTrue($lastCond);
        }
        return $this->isAlwaysTrue($loop->cond);
    }
    private function isAlwaysTrue(Expr $expr): bool
    {
        if ($expr instanceof ConstFetch) {
            return $expr->name->toLowerString() === 'true';
        }
        return $expr instanceof Int_ && $expr->value !== 0;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function hasBreakOrGoto(array $stmts): bool
    {
        $hasBreakOrGoto = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, static function (Node $node) use (&$hasBreakOrGoto): ?int {
            // nested scopes bring their own jump targets
            if ($node instanceof FunctionLike || $node instanceof ClassLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Break_ && !$node instanceof Goto_) {
                return null;
            }
            $hasBreakOrGoto = \true;
            return NodeVisitor::STOP_TRAVERSAL;
        });
        return $hasBreakOrGoto;
    }
    private function isTerminatedNode(Stmt $previousNode, Stmt $currentStmt): bool
    {
        if (in_array(get_class($previousNode), self::TERMINABLE_NODES, \true)) {
            return \true;
        }
        if ($previousNode instanceof Expression && ($previousNode->expr instanceof Exit_ || $previousNode->expr instanceof Throw_)) {
            return \true;
        }
        if ($previousNode instanceof Goto_) {
            return !$currentStmt instanceof Label;
        }
        return \false;
    }
    private function isTerminatedInLastStmtsSwitch(Switch_ $switch): bool
    {
        if ($switch->cases === []) {
            return \false;
        }
        // a break/continue jumps out of the Switch_, so the next stmt is still executable
        if ($this->hasEscapingJump($switch)) {
            return \false;
        }
        $hasDefault = \false;
        foreach ($switch->cases as $key => $case) {
            if (!$case->cond instanceof Expr) {
                $hasDefault = \true;
            }
            if ($case->stmts === [] && isset($switch->cases[$key + 1])) {
                continue;
            }
            if (!$this->isTerminatedInLastStmts($case->stmts)) {
                return \false;
            }
        }
        return $hasDefault;
    }
    private function hasEscapingJump(Switch_ $switch): bool
    {
        $hasEscapingJump = \false;
        foreach ($switch->cases as $case) {
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($case->stmts, static function (Node $node) use (&$hasEscapingJump): ?int {
                // nested scopes bring their own jump targets
                if ($node instanceof FunctionLike || $node instanceof ClassLike) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$node instanceof Break_ && !$node instanceof Continue_ && !$node instanceof Goto_) {
                    return null;
                }
                $hasEscapingJump = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            });
            if ($hasEscapingJump) {
                return \true;
            }
        }
        return \false;
    }
    private function isTerminatedInLastStmtsTryCatch(TryCatch $tryCatch): bool
    {
        if ($tryCatch->finally instanceof Finally_ && $this->isTerminatedInLastStmts($tryCatch->finally->stmts)) {
            return \true;
        }
        foreach ($tryCatch->catches as $catch) {
            if (!$this->isTerminatedInLastStmts($catch->stmts)) {
                return \false;
            }
        }
        return $this->isTerminatedInLastStmts($tryCatch->stmts);
    }
    private function isTerminatedInLastStmtsIf(If_ $if): bool
    {
        // Without ElseIf_[] and Else_, after If_ is possibly executable
        if ($if->elseifs === [] && !$if->else instanceof Else_) {
            return \false;
        }
        foreach ($if->elseifs as $elseif) {
            if (!$this->isTerminatedInLastStmts($elseif->stmts)) {
                return \false;
            }
        }
        if (!$this->isTerminatedInLastStmts($if->stmts)) {
            return \false;
        }
        if (!$if->else instanceof Else_) {
            return \false;
        }
        return $this->isTerminatedInLastStmts($if->else->stmts);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isTerminatedInLastStmts(array $stmts): bool
    {
        if ($stmts === []) {
            return \false;
        }
        $lastKey = array_key_last($stmts);
        $lastNode = $stmts[$lastKey];
        if ($lastNode instanceof Expression) {
            return $lastNode->expr instanceof Exit_ || $lastNode->expr instanceof Throw_;
        }
        return $lastNode instanceof Return_;
    }
}
