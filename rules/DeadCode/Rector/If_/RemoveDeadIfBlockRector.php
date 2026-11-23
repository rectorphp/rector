<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitor;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadIfBlockRector\RemoveDeadIfBlockRectorTest
 */
final class RemoveDeadIfBlockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ConditionInverter $conditionInverter;
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    public function __construct(ConditionInverter $conditionInverter, SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->conditionInverter = $conditionInverter;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove if, elseif, and else blocks that do not do anything', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value, $differentValue)
    {
        if ($value) {
        } elseif ($differentValue) {
        } else {
        }

        if ($differentValue) {
            echo 'different';
        } elseif ($value) {
        } else {
        }

        if ($differentValue) {
        } elseif ($value) {
            echo 'value';
        } else {
        }

        return $differentValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value, $differentValue)
    {
        if ($differentValue) {
            echo 'different';
        }

        if (!$differentValue && $value) {
            echo 'value';
        }

        return $differentValue;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return NodeVisitor::REMOVE_NODE|null|If_
     */
    public function refactor(Node $node)
    {
        if ($node->else instanceof Else_ && $node->else->stmts === []) {
            $node->else = null;
            return $this->refactor($node) ?? $node;
        }
        // An empty elseif still catches its case and stops later branches from running,
        // so it can only be removed when it is the last branch
        $lastElseIf = end($node->elseifs);
        if ($lastElseIf instanceof ElseIf_ && !$node->else instanceof Else_ && $lastElseIf->stmts === [] && !$this->sideEffectNodeDetector->detect($lastElseIf->cond)) {
            array_pop($node->elseifs);
            return $this->refactor($node) ?? $node;
        }
        if ($node->stmts !== []) {
            return null;
        }
        // Skip commented blocks because we can't know
        // if the comment will make sense after merging.
        if ($node->getComments() !== []) {
            return null;
        }
        if ($this->sideEffectNodeDetector->detect($node->cond)) {
            return null;
        }
        // When the if body is blank but it has an elseif,
        // merge the negated if condition with the elseif condition
        if ($node->elseifs !== []) {
            // A trailing else would run when the negated if condition is true,
            // which the original empty if body never did, so keep the code as is.
            // Later elseifs would run when the negated if condition is false too, so merge only a single elseif.
            if ($node->else instanceof Else_ || count($node->elseifs) > 1) {
                return null;
            }
            $firstElseIf = $node->elseifs[0];
            $cond = new BooleanAnd($this->conditionInverter->createInvertedCondition($node->cond), $firstElseIf->cond);
            $if = new If_($cond, ['stmts' => $firstElseIf->stmts]);
            return $this->refactor($if) ?? $if;
        }
        if ($node->else instanceof Else_) {
            $node->cond = $this->conditionInverter->createInvertedCondition($node->cond);
            $node->stmts = $node->else->stmts;
            $node->else = null;
            return $node;
        }
        return NodeVisitor::REMOVE_NODE;
    }
}
