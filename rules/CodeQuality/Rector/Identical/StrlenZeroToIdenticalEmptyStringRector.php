<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends \Rector\Core\Rector\AbstractRector
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes strlen comparison to 0 to direct empty string compare', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = strlen($value) === 0;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = $value === '';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->left instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processLeftIdentical($node, $node->left);
        }
        if ($node->right instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processRightIdentical($node, $node->right);
        }
        return null;
    }
    private function processLeftIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical, \PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\BinaryOp\Identical
    {
        if (!$this->isName($funcCall, 'strlen')) {
            return null;
        }
        if (!$this->valueResolver->isValue($identical->right, 0)) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        /** @var Expr $variable */
        $variable = $firstArg->value;
        return new \PhpParser\Node\Expr\BinaryOp\Identical($variable, new \PhpParser\Node\Scalar\String_(''));
    }
    private function processRightIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical, \PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\BinaryOp\Identical
    {
        if (!$this->isName($funcCall, 'strlen')) {
            return null;
        }
        if (!$this->valueResolver->isValue($identical->left, 0)) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0)) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        /** @var Expr $variable */
        $variable = $firstArg->value;
        return new \PhpParser\Node\Expr\BinaryOp\Identical($variable, new \PhpParser\Node\Scalar\String_(''));
    }
}
