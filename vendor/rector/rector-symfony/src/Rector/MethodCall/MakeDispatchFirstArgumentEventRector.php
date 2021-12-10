<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\MakeDispatchFirstArgumentEventRectorTest
 */
final class MakeDispatchFirstArgumentEventRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make event object a first argument of dispatch() method, event name as second', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch('event_name', new Event());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(new Event(), 'event_name');
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $firstArgumentValue = $firstArg->value;
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($firstArgumentValue)) {
            $this->refactorStringArgument($node);
            return $node;
        }
        $secondArg = $node->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $secondArgumentValue = $secondArg->value;
        if ($secondArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            $this->refactorGetCallFuncCall($node, $secondArgumentValue, $firstArgumentValue);
            return $node;
        }
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface'))) {
            return \true;
        }
        if (!$this->isName($methodCall->name, 'dispatch')) {
            return \true;
        }
        return !isset($methodCall->args[1]);
    }
    private function refactorStringArgument(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        // swap arguments
        [$methodCall->args[0], $methodCall->args[1]] = [$methodCall->args[1], $methodCall->args[0]];
        if ($this->isEventNameSameAsEventObjectClass($methodCall)) {
            unset($methodCall->args[1]);
        }
    }
    private function refactorGetCallFuncCall(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr) : void
    {
        if (!$this->isName($funcCall, 'get_class')) {
            return;
        }
        $firstArg = $funcCall->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return;
        }
        $getClassArgumentValue = $firstArg->value;
        if (!$this->nodeComparator->areNodesEqual($expr, $getClassArgumentValue)) {
            return;
        }
        unset($methodCall->args[1]);
    }
    /**
     * Is the event name just `::class`? We can remove it
     */
    private function isEventNameSameAsEventObjectClass(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $secondArg = $methodCall->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        if (!$secondArg->value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \false;
        }
        $classConst = $this->valueResolver->getValue($secondArg->value);
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $eventStaticType = $this->getType($firstArg->value);
        if (!$eventStaticType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $classConst === $eventStaticType->getClassName();
    }
}
