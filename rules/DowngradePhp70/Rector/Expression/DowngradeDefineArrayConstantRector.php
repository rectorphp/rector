<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\DowngradeDefineArrayConstantRectorTest
 */
final class DowngradeDefineArrayConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change array contant definition via define to const', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
define('ANIMALS', [
    'dog',
    'cat',
    'bird'
]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
const ANIMALS = [
    'dog',
    'cat',
    'bird'
];
CODE_SAMPLE
)]);
    }
    /**
     * @param Expression $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $funcCall = $node->expr;
        if ($this->shouldSkip($funcCall)) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $funcCall->args;
        /** @var String_ $arg0 */
        $arg0 = $args[0]->value;
        $arg0Value = $arg0->value;
        /** @var Array_ $arg1Value */
        $arg1Value = $args[1]->value;
        return new \PhpParser\Node\Stmt\Const_([new \PhpParser\Node\Const_($arg0Value, $arg1Value)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'define')) {
            return \true;
        }
        $args = $funcCall->args;
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($args, [0, 1])) {
            return \true;
        }
        /** @var Arg[] $args */
        if (!$args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            return \true;
        }
        if (!$args[1]->value instanceof \PhpParser\Node\Expr\Array_) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findParentByTypes($funcCall, [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class]);
    }
}
