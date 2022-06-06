<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\EarlyReturn\Rector\If_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Exit_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Continue_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Else_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ElseIf_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Throw_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split if statement, when if condition always break execution flow', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        } else {
            return 10;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        }

        return 10;
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if ($this->doesLastStatementBreakFlow($node)) {
            return null;
        }
        if ($node->elseifs !== []) {
            $originalNode = clone $node;
            $if = new If_($node->cond);
            $if->stmts = $node->stmts;
            $this->mirrorComments($if, $node);
            /** @var ElseIf_ $firstElseIf */
            $firstElseIf = \array_shift($node->elseifs);
            $node->cond = $firstElseIf->cond;
            $node->stmts = $firstElseIf->stmts;
            $this->mirrorComments($node, $firstElseIf);
            $nodesToReturnAfterNode = $this->getStatementsElseIfs($node);
            if ($originalNode->else instanceof Else_) {
                $node->else = null;
                $nodesToReturnAfterNode = \array_merge($nodesToReturnAfterNode, [$originalNode->else]);
            }
            return \array_merge([$if, $node], $nodesToReturnAfterNode);
        }
        if ($node->else !== null) {
            $stmts = $node->else->stmts;
            $node->else = null;
            return \array_merge([$node], $stmts);
        }
        return null;
    }
    /**
     * @return ElseIf_[]
     */
    private function getStatementsElseIfs(If_ $if) : array
    {
        $statements = [];
        foreach ($if->elseifs as $key => $elseif) {
            if ($this->doesLastStatementBreakFlow($elseif) && $elseif->stmts !== []) {
                continue;
            }
            $statements[] = $elseif;
            unset($if->elseifs[$key]);
        }
        return $statements;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_ $node
     */
    private function doesLastStatementBreakFlow($node) : bool
    {
        $lastStmt = \end($node->stmts);
        return !($lastStmt instanceof Return_ || $lastStmt instanceof Throw_ || $lastStmt instanceof Continue_ || $lastStmt instanceof Expression && $lastStmt->expr instanceof Exit_);
    }
}
