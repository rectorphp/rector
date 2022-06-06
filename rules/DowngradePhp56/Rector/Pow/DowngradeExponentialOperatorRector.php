<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp56\Rector\Pow;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Pow;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/pow-operator
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector\DowngradeExponentialOperatorRectorTest
 */
final class DowngradeExponentialOperatorRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes ** (exp) operator to pow(val, val2)', [new CodeSample('1**2;', 'pow(1, 2);')]);
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
    public function refactor(Node $node) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('pow', [$node->left, $node->right]);
    }
}
