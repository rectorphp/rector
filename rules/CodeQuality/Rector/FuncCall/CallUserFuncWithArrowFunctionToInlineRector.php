<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use Rector\Php74\NodeAnalyzer\ClosureArrowFunctionAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector\CallUserFuncWithArrowFunctionToInlineRectorTest
 */
final class CallUserFuncWithArrowFunctionToInlineRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClosureArrowFunctionAnalyzer $closureArrowFunctionAnalyzer;
    public function __construct(ClosureArrowFunctionAnalyzer $closureArrowFunctionAnalyzer)
    {
        $this->closureArrowFunctionAnalyzer = $closureArrowFunctionAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor `call_user_func()` with arrow function to direct call', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'call_user_func')) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        // change the node
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $firstArgValue = $firstArg->value;
        if ($firstArgValue instanceof ArrowFunction) {
            return $firstArgValue->expr;
        }
        if ($firstArgValue instanceof Closure) {
            return $this->closureArrowFunctionAnalyzer->matchArrowFunctionExpr($firstArgValue);
        }
        return null;
    }
}
