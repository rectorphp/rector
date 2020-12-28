<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Split if statement, when if condition always break execution flow',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->doesLastStatementBreakFlow($node)) {
            return null;
        }

        if ($node->elseifs !== []) {
            $if = new If_($node->cond);
            $if->stmts = $node->stmts;

            $this->addNodeBeforeNode($if, $node);

            /** @var ElseIf_ $firstElseIf */
            $firstElseIf = array_shift($node->elseifs);
            $node->cond = $firstElseIf->cond;
            $node->stmts = $firstElseIf->stmts;
            $this->mirrorComments($node, $firstElseIf);

            return $node;
        }

        if ($node->else !== null) {
            $this->addNodesAfterNode($node->else->stmts, $node);
            $node->else = null;
            return $node;
        }

        return null;
    }

    private function doesLastStatementBreakFlow(If_ $if): bool
    {
        $lastStmt = end($if->stmts);

        return ! ($lastStmt instanceof Return_
            || $lastStmt instanceof Throw_
            || $lastStmt instanceof Continue_
            || ($lastStmt instanceof Expression && $lastStmt->expr instanceof Exit_));
    }
}
