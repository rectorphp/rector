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
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector\DowngradeSpaceshipRectorTest
 */
final class DowngradeSpaceshipRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\Core\PhpParser\Node\NamedVariableFactory $namedVariableFactory)
    {
        $this->ifManipulator = $ifManipulator;
        $this->namedVariableFactory = $namedVariableFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Spaceship::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change spaceship with check equal, and ternary to result 0, -1, 1', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Expr\FuncCall
    {
        $leftVariableParam = new \PhpParser\Node\Expr\Variable('left');
        $rightVariableParam = new \PhpParser\Node\Expr\Variable('right');
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $leftParam = new \PhpParser\Node\Param($leftVariableParam);
        $rightParam = new \PhpParser\Node\Param($rightVariableParam);
        $anonymousFunction->params = [$leftParam, $rightParam];
        $if = $this->ifManipulator->createIfStmt(new \PhpParser\Node\Expr\BinaryOp\Identical($leftVariableParam, $rightVariableParam), new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Scalar\LNumber(0)));
        $anonymousFunction->stmts[0] = $if;
        $smaller = new \PhpParser\Node\Expr\BinaryOp\Smaller($leftVariableParam, $rightVariableParam);
        $ternaryIf = new \PhpParser\Node\Scalar\LNumber(-1);
        $ternaryElse = new \PhpParser\Node\Scalar\LNumber(1);
        $ternary = new \PhpParser\Node\Expr\Ternary($smaller, $ternaryIf, $ternaryElse);
        $anonymousFunction->stmts[1] = new \PhpParser\Node\Stmt\Return_($ternary);
        $assignVariable = $this->namedVariableFactory->createVariable($node, 'battleShipcompare');
        $assignExpression = $this->getAssignExpression($anonymousFunction, $assignVariable);
        $this->nodesToAddCollector->addNodeBeforeNode($assignExpression, $node, $this->file->getSmartFileInfo());
        return new \PhpParser\Node\Expr\FuncCall($assignVariable, [new \PhpParser\Node\Arg($node->left), new \PhpParser\Node\Arg($node->right)]);
    }
    private function getAssignExpression(\PhpParser\Node\Expr\Closure $closure, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, $closure));
    }
}
