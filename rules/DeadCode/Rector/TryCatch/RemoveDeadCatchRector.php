<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadCatchRector\RemoveDeadCatchRectorTest
 */
final class RemoveDeadCatchRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead catches', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // some code
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        } catch (Throwable $throwable) {
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
        try {
            // some code
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
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
     * @return TryCatch|null
     */
    public function refactor(Node $node)
    {
        $catches = $node->catches;
        if (\count($catches) === 1) {
            return null;
        }
        $hasChanged = \false;
        foreach ($catches as $key => $catchItem) {
            if ($this->isEmpty($catchItem->stmts)) {
                continue;
            }
            $catchItemStmt = $catchItem->stmts[0];
            if (!($catchItemStmt instanceof Expression && $catchItemStmt->expr instanceof Throw_)) {
                continue;
            }
            if (!$this->nodeComparator->areNodesEqual($catchItem->var, $catchItemStmt->expr->expr)) {
                continue;
            }
            unset($catches[$key]);
            $hasChanged = \true;
        }
        if (!$hasChanged || $catches === []) {
            return null;
        }
        $node->catches = $catches;
        return $node;
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
