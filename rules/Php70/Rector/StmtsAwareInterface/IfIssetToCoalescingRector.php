<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector\IfIssetToCoalescingRectorTest
 */
final class IfIssetToCoalescingRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `if` with `isset` and `return` to coalesce', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function resolve($key)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        return 'fallback value';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function resolve($key)
    {
        return $this->items[$key] ?? 'fallback value';
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Expr) {
                continue;
            }
            $previousStmt = $node->stmts[$key - 1] ?? null;
            if (!$previousStmt instanceof If_) {
                continue;
            }
            if (!$previousStmt->cond instanceof Isset_) {
                continue;
            }
            $ifOnlyStmt = $this->matchBareIfOnlyStmt($previousStmt);
            if (!$ifOnlyStmt instanceof Return_) {
                continue;
            }
            if (!$ifOnlyStmt->expr instanceof Expr) {
                continue;
            }
            $ifIsset = $previousStmt->cond;
            if (!$this->nodeComparator->areNodesEqual($ifOnlyStmt->expr, $ifIsset->vars[0])) {
                continue;
            }
            unset($node->stmts[$key - 1]);
            $stmt->expr = new Coalesce($ifOnlyStmt->expr, $stmt->expr);
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    private function matchBareIfOnlyStmt(If_ $if) : ?Stmt
    {
        if ($if->else instanceof Else_) {
            return null;
        }
        if ($if->elseifs !== []) {
            return null;
        }
        if (\count($if->stmts) !== 1) {
            return null;
        }
        return $if->stmts[0];
    }
}
