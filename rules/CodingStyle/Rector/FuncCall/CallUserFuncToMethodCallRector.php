<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/1596250/1348344
 *
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector\CallUserFuncToMethodCallRectorTest
 */
final class CallUserFuncToMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory
     */
    private $arrayCallableToMethodCallFactory;
    public function __construct(\Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory $arrayCallableToMethodCallFactory)
    {
        $this->arrayCallableToMethodCallFactory = $arrayCallableToMethodCallFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor call_user_func() on known class method to a method call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $result = \call_user_func([$this->property, 'method'], $args);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $result = $this->property->method($args);
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
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgValue = $node->args[0]->value;
        if (!$firstArgValue instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $methodCall = $this->arrayCallableToMethodCallFactory->create($firstArgValue);
        if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $originalArgs = $node->args;
        unset($originalArgs[0]);
        $methodCall->args = $originalArgs;
        return $methodCall;
    }
}
