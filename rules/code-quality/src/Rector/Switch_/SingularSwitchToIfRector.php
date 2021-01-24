<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector\SingularSwitchToIfRectorTest
 */
final class SingularSwitchToIfRector extends AbstractRector
{
    /**
     * @var \Rector\Renaming\NodeManipulator\SwitchManipulator
     */
    private $switchManipulator;

    public function __construct(\Rector\Renaming\NodeManipulator\SwitchManipulator $switchManipulator)
    {
        $this->switchManipulator = $switchManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change switch with only 1 check to if', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($value)
    {
        $result = 1;
        switch ($value) {
            case 100:
            $result = 1000;
        }

        return $result;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($value)
    {
        $result = 1;
        if ($value === 100) {
            $result = 1000;
        }

        return $result;
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
        return [\PhpParser\Node\Stmt\Switch_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->cases) !== 1) {
            return null;
        }

        $onlyCase = $node->cases[0];

        $ifNode = new If_(new Equal($node->cond, $onlyCase->cond));
        $ifNode->stmts = $this->switchManipulator->removeBreakNodes($onlyCase->stmts);

        return $node;
    }
}
