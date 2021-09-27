<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/fuuEF
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\CallUserFuncWithArrowFunctionToInlineRectorTest
 */
final class CallUserFuncWithArrowFunctionToInlineRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer
     */
    private $closureArrowFunctionAnalyzer;
    public function __construct(\Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer $closureArrowFunctionAnalyzer)
    {
        $this->closureArrowFunctionAnalyzer = $closureArrowFunctionAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor call_user_func() with arrow function to direct call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $result = \call_user_func(fn () => 100);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $result = 100;
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'call_user_func')) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        // change the node
        if (!isset($node->args[0])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgValue = $firstArg->value;
        if ($firstArgValue instanceof \PhpParser\Node\Expr\ArrowFunction) {
            return $firstArgValue->expr;
        }
        if ($firstArgValue instanceof \PhpParser\Node\Expr\Closure) {
            return $this->closureArrowFunctionAnalyzer->matchArrowFunctionExpr($firstArgValue);
        }
        return null;
    }
}
