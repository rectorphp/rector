<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\TryCatch;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Finally_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\Node\Stmt\Throw_;
use RectorPrefix20220606\PhpParser\Node\Stmt\TryCatch;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
     * @return mixed[]|null|\PhpParser\Node\Stmt\TryCatch
     */
    public function refactor(Node $node)
    {
        $isEmptyFinallyStmts = !$node->finally instanceof Finally_ || $this->isEmpty($node->finally->stmts);
        // not empty stmts on finally always executed
        if (!$isEmptyFinallyStmts) {
            return null;
        }
        if ($this->isEmpty($node->stmts)) {
            $this->removeNode($node);
            return $node;
        }
        if (\count($node->catches) !== 1) {
            return null;
        }
        $onlyCatch = $node->catches[0];
        if ($this->isEmpty($onlyCatch->stmts)) {
            return null;
        }
        $onlyCatchStmt = $onlyCatch->stmts[0];
        if (!$onlyCatchStmt instanceof Throw_) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($onlyCatch->var, $onlyCatchStmt->expr)) {
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
