<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\SwitchManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector\BinarySwitchToIfElseRectorTest
 */
final class BinarySwitchToIfElseRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\SwitchManipulator
     */
    private $switchManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(SwitchManipulator $switchManipulator, ExprAnalyzer $exprAnalyzer)
    {
        $this->switchManipulator = $switchManipulator;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes switch with 2 options to if-else', [new CodeSample(<<<'CODE_SAMPLE'
switch ($foo) {
    case 'my string':
        $result = 'ok';
    break;

    default:
        $result = 'not ok';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($foo == 'my string') {
    $result = 'ok';
} else {
    $result = 'not ok';
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
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->cases) > 2) {
            return null;
        }
        // avoid removal of cases if it goes to be skipped next
        $cases = $node->cases;
        /** @var Case_ $firstCase */
        $firstCase = \array_shift($cases);
        if (!$firstCase->cond instanceof Expr) {
            return null;
        }
        if ($this->exprAnalyzer->isDynamicExpr($firstCase->cond)) {
            return null;
        }
        $secondCase = \array_shift($cases);
        // special case with empty first case → ||
        $isFirstCaseEmpty = $firstCase->stmts === [];
        if ($isFirstCaseEmpty && $secondCase instanceof Case_ && $secondCase->cond instanceof Expr) {
            $else = new BooleanOr(new Equal($node->cond, $firstCase->cond), new Equal($node->cond, $secondCase->cond));
            $ifNode = new If_($else);
            $ifNode->stmts = $this->switchManipulator->removeBreakNodes($secondCase->stmts);
            return $ifNode;
        }
        $ifNode = new If_(new Equal($node->cond, $firstCase->cond));
        $ifNode->stmts = $this->switchManipulator->removeBreakNodes($firstCase->stmts);
        // just one condition
        if (!$secondCase instanceof Case_) {
            return $ifNode;
        }
        if ($secondCase->cond instanceof Expr) {
            // has condition
            $equal = new Equal($node->cond, $secondCase->cond);
            $ifNode->elseifs[] = new ElseIf_($equal, $this->switchManipulator->removeBreakNodes($secondCase->stmts));
        } else {
            // defaults
            $ifNode->else = new Else_($this->switchManipulator->removeBreakNodes($secondCase->stmts));
        }
        return $ifNode;
    }
}
