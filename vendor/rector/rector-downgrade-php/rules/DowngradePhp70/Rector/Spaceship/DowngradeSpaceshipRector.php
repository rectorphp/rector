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
        $hasFound = \false;
        $assignVariable = $this->namedVariableFactory->createVariable($node, 'battleShipcompare');
        $this->traverseNodesWithCallable($node, static function (Node $node) use(&$hasFound, $assignVariable) : ?FuncCall {
            if (!$node instanceof Spaceship) {
                return null;
            }
            $hasFound = \true;
            return new FuncCall($assignVariable, [new Arg($node->left), new Arg($node->right)]);
        });
        if ($hasFound === \false) {
            return null;
        }
        $anonymousFunction = $this->createAnonymousFunction();
        $assignExpression = new Expression(new Assign($assignVariable, $anonymousFunction));
        return [$assignExpression, $node];
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
