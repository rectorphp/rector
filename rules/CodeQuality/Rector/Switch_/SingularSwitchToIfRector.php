<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\SwitchManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Switch_\SingularSwitchToIfRector\SingularSwitchToIfRectorTest
 */
final class SingularSwitchToIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\SwitchManipulator
     */
    private $switchManipulator;
    public function __construct(SwitchManipulator $switchManipulator)
    {
        $this->switchManipulator = $switchManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change switch with only 1 check to if', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     * @return Node\Stmt[]|If_|null
     */
    public function refactor(Node $node)
    {
        if (\count($node->cases) !== 1) {
            return null;
        }
        $onlyCase = $node->cases[0];
        // only default → basically unwrap
        if ($onlyCase->cond === null) {
            return $onlyCase->stmts;
        }
        $if = new If_(new Identical($node->cond, $onlyCase->cond));
        $if->stmts = $this->switchManipulator->removeBreakNodes($onlyCase->stmts);
        return $if;
    }
}
