<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
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
final class CallUserFuncToMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory
     */
    private $arrayCallableToMethodCallFactory;
    public function __construct(ArrayCallableToMethodCallFactory $arrayCallableToMethodCallFactory)
    {
        $this->arrayCallableToMethodCallFactory = $arrayCallableToMethodCallFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor call_user_func() on known class method to a method call', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'call_user_func')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArgValue = $node->getArgs()[0]->value;
        if (!$firstArgValue instanceof Array_) {
            return null;
        }
        $methodCall = $this->arrayCallableToMethodCallFactory->create($firstArgValue);
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $originalArgs = $node->args;
        unset($originalArgs[0]);
        $methodCall->args = $originalArgs;
        return $methodCall;
    }
}
