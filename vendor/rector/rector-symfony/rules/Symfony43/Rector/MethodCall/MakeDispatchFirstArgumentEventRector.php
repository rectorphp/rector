<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

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
 * @changelog https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
 *
 * @see \Rector\Symfony\Tests\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\MakeDispatchFirstArgumentEventRectorTest
 */
final class MakeDispatchFirstArgumentEventRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make event object a first argument of dispatch() method, event name as second', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $firstArgumentValue = $firstArg->value;
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($firstArgumentValue)) {
            $this->refactorStringArgument($node);
            return $node;
        }
        $secondArg = $node->args[1];
        if (!$secondArg instanceof Arg) {
            return null;
        }
        $secondArgumentValue = $secondArg->value;
        if ($secondArgumentValue instanceof FuncCall) {
            $this->refactorGetCallFuncCall($node, $secondArgumentValue, $firstArgumentValue);
            return $node;
        }
        return null;
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'dispatch')) {
            return \true;
        }
        if (!$this->isObjectType($methodCall->var, new ObjectType('Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface'))) {
            return \true;
        }
        return !isset($methodCall->args[1]);
    }
    private function refactorStringArgument(MethodCall $methodCall) : void
    {
        // swap arguments
        [$methodCall->args[0], $methodCall->args[1]] = [$methodCall->args[1], $methodCall->args[0]];
        if ($this->isEventNameSameAsEventObjectClass($methodCall)) {
            unset($methodCall->args[1]);
        }
    }
    private function refactorGetCallFuncCall(MethodCall $methodCall, FuncCall $funcCall, Expr $expr) : void
    {
        if (!$this->isName($funcCall, 'get_class')) {
            return;
        }
        $firstArg = $funcCall->args[0];
        if (!$firstArg instanceof Arg) {
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
    private function isEventNameSameAsEventObjectClass(MethodCall $methodCall) : bool
    {
        $secondArg = $methodCall->args[1];
        if (!$secondArg instanceof Arg) {
            return \false;
        }
        if (!$secondArg->value instanceof ClassConstFetch) {
            return \false;
        }
        $classConst = $this->valueResolver->getValue($secondArg->value);
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof Arg) {
            return \false;
        }
        $eventStaticType = $this->getType($firstArg->value);
        if (!$eventStaticType instanceof ObjectType) {
            return \false;
        }
        return $classConst === $eventStaticType->getClassName();
    }
}
