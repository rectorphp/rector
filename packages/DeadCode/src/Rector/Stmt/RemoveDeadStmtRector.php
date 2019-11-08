<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector\RemoveDeadStmtRectorTest
 */
final class RemoveDeadStmtRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes dead code statements', [
            new CodeSample(
                <<<'PHP'
$value = 5;
$value;
PHP
                ,
                <<<'PHP'
$value = 5;
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    public function refactor(Node $node): ?Node
    {
        $livingCode = $this->keepLivingCodeFromExpr($node->expr);

        if ($livingCode === []) {
            return $this->savelyRemoveNode($node);
        }

        $firstExpr = array_shift($livingCode);
        $node->expr = $firstExpr;

        foreach ($livingCode as $expr) {
            $this->addNodeAfterNode(new Expression($expr), $node);
        }

        return null;
    }

    protected function savelyRemoveNode(Node $node): ?Node
    {
        if ($node->getComments() !== []) {
            $nop = new Nop();
            $nop->setAttribute('comments', $node->getComments());
            return $nop;
        }

        $this->removeNode($node);

        return null;
    }
}
