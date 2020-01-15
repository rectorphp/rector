<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node;
use PhpParser\NodeDumper;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**

 * @see \Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\SplitIfsRectorTest
 */
final class SplitIfsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Merges nested if statements', [
            new CodeSample(
                <<<'PHP'
class SomeClass {
    public function run()
    {
        if ($cond1) {
            return 'foo';
        } else {
            return 'bar';
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass {
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }

        return 'bar';
    }
}
PHP

            )
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
        $lastStmt = end($node->stmts);

        if (!($lastStmt instanceof Return_
            || $lastStmt instanceof Throw_
            || ($lastStmt instanceof Expression && $lastStmt->expr instanceof Exit_))
        ) {
            return null;
        }

        if ($node->elseifs) {
            $newNode = new If_($node->cond);
            $newNode->stmts =  $node->stmts;
            $this->addNodeBeforeNode($newNode, $node);
            /** @var Node\Stmt\ElseIf_ $firstElseIf */
            $firstElseIf = array_shift($node->elseifs);
            $node->cond = $firstElseIf->cond;
            $node->stmts = $firstElseIf->stmts;
            return $node;
        } elseif ($node->else !== null) {
            foreach ($node->else->stmts as $stmt) {
                $this->addNodeAfterNode($stmt, $node);
            }
            $node->else = null;
            return $node;
        }

        return null;
    }
}
