<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\If_;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\FunctionLike\RemoveUnusedElseForReturnedValueRector\RemoveUnusedElseForReturnedValueRectorTest
 */
final class RemoveUnusedElseForReturnedValueRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove if for last else, if previous values were awlways returned', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return 5;
        } else {
            return 10;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return 5;
        }

        return 10;
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
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->traverseNodesWithCallable((array) $node->getStmts(), function (Node $node) {
            if (! $node instanceof If_) {
                return null;
            }

            if (! $this->ifManipulator->isWithElseAlwaysReturnValue($node)) {
                return null;
            }

            foreach ($node->else->stmts as $elseStmt) {
                $this->addNodeAfterNode($elseStmt, $node);
            }

            $node->else = null;
        });

        return $node;
    }
}
