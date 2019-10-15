<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\SimplifyIfElseWithSameContentRector\SimplifyIfElseWithSameContentRectorTest
 */
final class SimplifyIfElseWithSameContentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove if/else if they have same content', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if (true) {
            return 1;
        } else {
            return 1;
        }
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return 1;
    }
}
PHP
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
        if ($node->else === null) {
            return null;
        }

        if (! $this->isIfWithConstantReturns($node)) {
            return null;
        }

        foreach ($node->stmts as $stmt) {
            $this->addNodeBeforeNode($stmt, $node);
        }

        $this->removeNode($node);

        return $node;
    }

    private function isIfWithConstantReturns(If_ $if): bool
    {
        $possibleContents = [];
        $possibleContents[] = $this->print($if->stmts);

        foreach ($if->elseifs as $elseif) {
            $possibleContents[] = $this->print($elseif->stmts);
        }

        $possibleContents[] = $this->print($if->else->stmts);

        $uniqueContents = array_unique($possibleContents);

        // only one content for all
        return count($uniqueContents) === 1;
    }
}
