<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\Constant\ConstantBooleanType;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
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
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ExprAnalyzer $exprAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Reduce always false in a if ( || ) condition', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (!$conditionStaticType instanceof ConstantBooleanType) {
            return null;
        }
        if ($conditionStaticType->getValue()) {
            return null;
        }
        $hasNonTypedFromParam = $this->betterNodeFinder->findFirst($booleanOr->left, function (Node $node) : bool {
            return $node instanceof Variable && $this->exprAnalyzer->isNonTypedFromParam($node);
        });
        if ($hasNonTypedFromParam instanceof Node) {
            return null;
        }
        $node->cond = $booleanOr->right;
        return $node;
    }
}
