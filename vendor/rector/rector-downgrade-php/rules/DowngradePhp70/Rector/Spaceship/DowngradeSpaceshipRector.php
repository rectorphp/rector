<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Spaceship;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeFactory\NamedVariableFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector\DowngradeSpaceshipRectorTest
 */
final class DowngradeSpaceshipRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\NodeFactory\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(IfManipulator $ifManipulator, NamedVariableFactory $namedVariableFactory)
    {
        $this->ifManipulator = $ifManipulator;
        $this->namedVariableFactory = $namedVariableFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Return_::class, If_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change spaceship with check equal, and ternary to result 0, -1, 1', [new CodeSample(<<<'CODE_SAMPLE'
return $a <=> $b;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$battleShipcompare = function ($left, $right) {
    if ($left === $right) {
        return 0;
    }
    return $left < $right ? -1 : 1;
};
return $battleShipcompare($a, $b);
CODE_SAMPLE
)]);
    }
    /**
     * @param Return_|If_ $node
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_ && $node->cond instanceof Spaceship) {
            $spaceship = $node->cond;
        } elseif ($node instanceof Return_ && $node->expr instanceof Spaceship) {
            $spaceship = $node->expr;
        } else {
            return null;
        }
        $anonymousFunction = $this->createAnonymousFunction();
        $assignVariable = $this->namedVariableFactory->createVariable($node, 'battleShipcompare');
        $assignExpression = $this->getAssignExpression($anonymousFunction, $assignVariable);
        $compareFuncCall = new FuncCall($assignVariable, [new Arg($spaceship->left), new Arg($spaceship->right)]);
        if ($node instanceof Return_) {
            $node->expr = $compareFuncCall;
        } elseif ($node instanceof If_) {
            $node->cond = $compareFuncCall;
        }
        return [$assignExpression, $node];
    }
    private function getAssignExpression(Closure $closure, Variable $variable) : Expression
    {
        return new Expression(new Assign($variable, $closure));
    }
    private function createAnonymousFunction() : Closure
    {
        $leftVariableParam = new Variable('left');
        $rightVariableParam = new Variable('right');
        $leftParam = new Param($leftVariableParam);
        $rightParam = new Param($rightVariableParam);
        $if = $this->ifManipulator->createIfStmt(new Identical($leftVariableParam, $rightVariableParam), new Return_(new LNumber(0)));
        $smaller = new Smaller($leftVariableParam, $rightVariableParam);
        $ternaryIf = new LNumber(-1);
        $ternaryElse = new LNumber(1);
        $ternary = new Ternary($smaller, $ternaryIf, $ternaryElse);
        return new Closure(['params' => [$leftParam, $rightParam], 'stmts' => [$if, new Return_($ternary)]]);
    }
}
