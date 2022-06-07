<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\Rector\Pow;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Pow;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/pow-operator
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector\DowngradeExponentialAssignmentOperatorRectorTest
 */
final class DowngradeExponentialAssignmentOperatorRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove exponential assignment operator **=', [new CodeSample('$a **= 3;', '$a = pow($a, 3);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Pow::class];
    }
    /**
     * @param Pow $node
     */
    public function refactor(Node $node) : Assign
    {
        $powFuncCall = $this->nodeFactory->createFuncCall('pow', [$node->var, $node->expr]);
        return new Assign($node->var, $powFuncCall);
    }
}
