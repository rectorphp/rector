<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\For_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function run($someObject)
    {
        $value = 5;
        if ($value) {
        }

        if ($someObject->run()) {
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
    public function run($someObject)
    {
        $value = 5;
        if ($someObject->run()) {
        }

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
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof If_) {
            $this->processIf($node);
            return null;
        }
        if ($node instanceof Foreach_) {
            $this->processForeach($node);
            return null;
        }
        // For
        if ($node->stmts !== []) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function processIf(If_ $if) : void
    {
        if ($if->stmts !== []) {
            return;
        }
        if ($if->elseifs !== []) {
            return;
        }
        if ($if->else !== null) {
            $if->cond = $this->conditionInverter->createInvertedCondition($if->cond);
            $if->stmts = $if->else->stmts;
            $if->else = null;
            return;
        }
        if ($this->isNodeWithSideEffect($if->cond)) {
            return;
        }
        $this->removeNode($if);
    }
    private function processForeach(Foreach_ $foreach) : void
    {
        if ($foreach->stmts !== []) {
            return;
        }
        if ($this->isNodeWithSideEffect($foreach->expr)) {
            return;
        }
        $this->removeNode($foreach);
    }
    private function isNodeWithSideEffect(Expr $expr) : bool
    {
        if ($expr instanceof Variable) {
            return \false;
        }
        if ($expr instanceof Scalar) {
            return \false;
        }
        return !$this->valueResolver->isTrueOrFalse($expr);
    }
}
