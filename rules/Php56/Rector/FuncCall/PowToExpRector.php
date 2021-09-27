<?php

declare (strict_types=1);
namespace Rector\Php56\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php56\Rector\FuncCall\PowToExpRector\PowToExpRectorTest
 */
final class PowToExpRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes pow(val, val2) to ** (exp) parameter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('pow(1, 2);', '1**2;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'pow')) {
            return null;
        }
        if (\count($node->args) !== 2) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($node->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArgument */
        $firstArgument = $node->args[0];
        /** @var Arg $secondArgument */
        $secondArgument = $node->args[1];
        return new \PhpParser\Node\Expr\BinaryOp\Pow($firstArgument->value, $secondArgument->value);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::EXP_OPERATOR;
    }
}
