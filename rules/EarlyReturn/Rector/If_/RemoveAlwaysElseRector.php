<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Split if statement, when if condition always break execution flow', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->doesLastStatementBreakFlow($node)) {
            return null;
        }
        if ($node->elseifs !== []) {
            $originalNode = clone $node;
            $if = new \PhpParser\Node\Stmt\If_($node->cond);
            $if->stmts = $node->stmts;
            $this->nodesToAddCollector->addNodeBeforeNode($if, $node);
            $this->mirrorComments($if, $node);
            /** @var ElseIf_ $firstElseIf */
            $firstElseIf = \array_shift($node->elseifs);
            $node->cond = $firstElseIf->cond;
            $node->stmts = $firstElseIf->stmts;
            $this->mirrorComments($node, $firstElseIf);
            $statements = $this->getStatementsElseIfs($node);
            if ($statements !== []) {
                $this->nodesToAddCollector->addNodesAfterNode($statements, $node);
            }
            if ($originalNode->else instanceof \PhpParser\Node\Stmt\Else_) {
                $node->else = null;
                $this->nodesToAddCollector->addNodeAfterNode($originalNode->else, $node);
            }
            return $node;
        }
        if ($node->else !== null) {
            $this->nodesToAddCollector->addNodesAfterNode($node->else->stmts, $node);
            $node->else = null;
            return $node;
        }
        return null;
    }
    /**
     * @return ElseIf_[]
     */
    private function getStatementsElseIfs(\PhpParser\Node\Stmt\If_ $if) : array
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
     * @param \PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Stmt\If_ $node
     */
    private function doesLastStatementBreakFlow($node) : bool
    {
        $lastStmt = \end($node->stmts);
        return !($lastStmt instanceof \PhpParser\Node\Stmt\Return_ || $lastStmt instanceof \PhpParser\Node\Stmt\Throw_ || $lastStmt instanceof \PhpParser\Node\Stmt\Continue_ || $lastStmt instanceof \PhpParser\Node\Stmt\Expression && $lastStmt->expr instanceof \PhpParser\Node\Expr\Exit_);
    }
}
