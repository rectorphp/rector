<?php

declare (strict_types=1);
namespace Rector\Php56\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php56\Rector\FuncCall\PowToExpRector\PowToExpRectorTest
 */
final class PowToExpRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes `pow(val, val2)` to `**` (exp) parameter', [new CodeSample('pow(1, 2);', '1**2;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'pow')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstExpr = $node->getArgs()[0]->value;
        $secondExpr = $node->getArgs()[1]->value;
        return new Pow($firstExpr, $secondExpr);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::EXP_OPERATOR;
    }
}
