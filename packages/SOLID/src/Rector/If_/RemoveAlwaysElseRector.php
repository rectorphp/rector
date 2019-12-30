<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends AbstractRector
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
        return new RectorDefinition('Remove if for last else, if previous values were throw', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
        if (! $this->ifManipulator->isEarlyElse($node)) {
            return null;
        }

        foreach ($node->else->stmts as $elseStmt) {
            $this->addNodeAfterNode($elseStmt, $node);
        }

        $node->else = null;

        return $node;
    }
}
