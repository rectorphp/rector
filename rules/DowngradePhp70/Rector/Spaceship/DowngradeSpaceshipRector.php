<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Spaceship;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector\DowngradeSpaceshipRectorTest
 */
final class DowngradeSpaceshipRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Spaceship::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change spaceship with check equal, and ternary to result 0, -1, 1',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
return $a <=> $b;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
return (function ($a, $a) {
    if ($a === $b) {
        return 0;
    }
    return $a < $b ? -1 : 1;
})($a, $b);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Spaceship $node
     */
    public function refactor(Node $node): ?Node
    {
        $anonymousFunction = new Closure();
        $leftVariableParam = new Variable((string) $this->getName($node->left));
        $leftParam = new Param($leftVariableParam);
        $rightVariableParam = new Variable((string) $this->getName($node->right));
        $rightParam = new Param($rightVariableParam);
        $anonymousFunction->params = [$leftParam, $rightParam];

        $if = $this->ifManipulator->createIfExpr(
            new Identical($leftVariableParam, $rightVariableParam),
            new Return_(new LNumber(0))
        );
        $anonymousFunction->stmts[0] = $if;

        $smaller = new Smaller($leftVariableParam, $rightVariableParam);
        $ternaryIf = new LNumber(-1);
        $ternaryElse = new LNumber(1);
        $ternary = new Ternary($smaller, $ternaryIf, $ternaryElse);
        $anonymousFunction->stmts[1] = new Return_($ternary);

        return new FuncCall($anonymousFunction, [new Arg($node->left), new Arg($node->right)]);
    }
}
