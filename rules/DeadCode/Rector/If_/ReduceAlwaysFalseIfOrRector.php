<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\If_;
use Rector\DeadCode\NodeAnalyzer\SafeLeftTypeBooleanAndOrAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector\ReduceAlwaysFalseIfOrRectorTest
 */
final class ReduceAlwaysFalseIfOrRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SafeLeftTypeBooleanAndOrAnalyzer $safeLeftTypeBooleanAndOrAnalyzer;
    public function __construct(SafeLeftTypeBooleanAndOrAnalyzer $safeLeftTypeBooleanAndOrAnalyzer)
    {
        $this->safeLeftTypeBooleanAndOrAnalyzer = $safeLeftTypeBooleanAndOrAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Reduce always false in a `if (a || b)` condition', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $number)
    {
        if (! is_int($number) || $number > 50) {
            return 'yes';
        }

        return 'no';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $number)
    {
        if ($number > 50) {
            return 'yes';
        }

        return 'no';
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->cond instanceof BooleanOr) {
            return null;
        }
        $booleanOr = $node->cond;
        $conditionStaticType = $this->getType($booleanOr->left);
        if (!$conditionStaticType->isFalse()->yes()) {
            return null;
        }
        if (!$this->safeLeftTypeBooleanAndOrAnalyzer->isSafe($booleanOr)) {
            return null;
        }
        $node->cond = $booleanOr->right;
        return $node;
    }
}
