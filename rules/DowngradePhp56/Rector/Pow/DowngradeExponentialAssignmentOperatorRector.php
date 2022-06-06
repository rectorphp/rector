<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp56\Rector\Pow;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Pow;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
