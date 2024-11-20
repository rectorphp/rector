<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector\RemoveDeadTryCatchRectorTest
 */
final class RemoveDeadTryCatchRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead try/catch', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // some code
        }
        catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [TryCatch::class];
    }
    /**
     * @param TryCatch $node
     * @return Stmt[]|null|int
     */
    public function refactor(Node $node)
    {
        $isEmptyFinallyStmts = !$node->finally instanceof Finally_ || $this->isEmpty($node->finally->stmts);
        // not empty stmts on finally always executed
        if (!$isEmptyFinallyStmts) {
            return null;
        }
        if ($this->isEmpty($node->stmts)) {
            return NodeVisitor::REMOVE_NODE;
        }
        if (\count($node->catches) !== 1) {
            return null;
        }
        $onlyCatch = $node->catches[0];
        if ($this->isEmpty($onlyCatch->stmts)) {
            return null;
        }
        $onlyCatchStmt = $onlyCatch->stmts[0];
        if (!($onlyCatchStmt instanceof Expression && $onlyCatchStmt->expr instanceof Throw_)) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($onlyCatch->var, $onlyCatchStmt->expr->expr)) {
            return null;
        }
        return $node->stmts;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isEmpty(array $stmts) : bool
    {
        if ($stmts === []) {
            return \true;
        }
        if (\count($stmts) > 1) {
            return \false;
        }
        return $stmts[0] instanceof Nop;
    }
}
