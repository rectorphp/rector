<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadIfForeachForRector\RemoveDeadIfForeachForRectorTest
 */
final class RemoveDeadIfForeachForRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    public function __construct(ConditionInverter $conditionInverter)
    {
        $this->conditionInverter = $conditionInverter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove if, foreach and for that does not do anything', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
        }

        foreach ($values as $value) {
        }

        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return $value;
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
        return [For_::class, If_::class, Foreach_::class];
    }
    /**
     * @param For_|If_|Foreach_ $node
     * @return \PhpParser\Node|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            if ($node->stmts !== []) {
                return null;
            }
            if ($node->elseifs !== []) {
                return null;
            }
            // useless if ()
            if (!$node->else instanceof Else_) {
                if ($this->hasNodeSideEffect($node->cond)) {
                    return null;
                }
                return NodeTraverser::REMOVE_NODE;
            }
            $node->cond = $this->conditionInverter->createInvertedCondition($node->cond);
            $node->stmts = $node->else->stmts;
            $node->else = null;
            return $node;
        }
        // nothing to "for"
        if ($node->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return null;
    }
    private function hasNodeSideEffect(Expr $expr) : bool
    {
        return $this->betterNodeFinder->hasInstancesOf($expr, [CallLike::class, Assign::class]);
    }
}
