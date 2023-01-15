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
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeFactory\NamedVariableFactory;
use Rector\PostRector\Collector\NodesToAddCollector;
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(IfManipulator $ifManipulator, NamedVariableFactory $namedVariableFactory, NodesToAddCollector $nodesToAddCollector)
    {
        $this->ifManipulator = $ifManipulator;
        $this->namedVariableFactory = $namedVariableFactory;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Spaceship::class];
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
     * @param Spaceship $node
     */
    public function refactor(Node $node) : FuncCall
    {
        $leftVariableParam = new Variable('left');
        $rightVariableParam = new Variable('right');
        $anonymousFunction = new Closure();
        $leftParam = new Param($leftVariableParam);
        $rightParam = new Param($rightVariableParam);
        $anonymousFunction->params = [$leftParam, $rightParam];
        $if = $this->ifManipulator->createIfStmt(new Identical($leftVariableParam, $rightVariableParam), new Return_(new LNumber(0)));
        $anonymousFunction->stmts[0] = $if;
        $smaller = new Smaller($leftVariableParam, $rightVariableParam);
        $ternaryIf = new LNumber(-1);
        $ternaryElse = new LNumber(1);
        $ternary = new Ternary($smaller, $ternaryIf, $ternaryElse);
        $anonymousFunction->stmts[1] = new Return_($ternary);
        $assignVariable = $this->namedVariableFactory->createVariable($node, 'battleShipcompare');
        $assignExpression = $this->getAssignExpression($anonymousFunction, $assignVariable);
        $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $node);
        return new FuncCall($assignVariable, [new Arg($node->left), new Arg($node->right)]);
    }
    private function getAssignExpression(Closure $closure, Variable $variable) : Expression
    {
        return new Expression(new Assign($variable, $closure));
    }
}
