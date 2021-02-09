<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        if (! $stdClass instanceof stdClass) {
            return false;
        }
        return true;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        return true;
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
        return $node;
    }
}
