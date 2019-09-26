<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
 * @see \Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\MakeDispatchFirstArgumentEventRectorTest
 */
final class MakeDispatchFirstArgumentEventRector extends AbstractRector
{
    /**
     * @var string
     */
    private $eventDispatcherClass;

    public function __construct(
        string $eventDispatcherClass = 'Symfony\Component\EventDispatcher\EventDispatcherInterface'
    ) {
        $this->eventDispatcherClass = $eventDispatcherClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make event object a first argument of dispatch() method, event name as second', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch('event_name', new Event());
    }
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(new Event(), 'event_name');
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($this->isStringOrUnionStringOnlyType($firstArgumentValue)) {
            return $this->refactorStringArgument($node);
        }

        $secondArgumentValue = $node->args[1]->value;
        if ($secondArgumentValue instanceof FuncCall) {
            return $this->refactorGetCallFuncCall($node, $secondArgumentValue, $firstArgumentValue);
        }

        return null;
    }

    /**
     * Is the event name just `::class`?
     * We can remove it
     */
    private function isEventNameSameAsEventObjectClass(MethodCall $methodCall): bool
    {
        if (! $methodCall->args[1]->value instanceof ClassConstFetch) {
            return false;
        }

        $classConst = $this->getValue($methodCall->args[1]->value);
        $eventStaticType = $this->getStaticType($methodCall->args[0]->value);

        if (! $eventStaticType instanceof ObjectType) {
            return false;
        }

        return $classConst === $eventStaticType->getClassName();
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall, $this->eventDispatcherClass)) {
            return true;
        }

        if (! $this->isName($methodCall, 'dispatch')) {
            return true;
        }

        if (! isset($methodCall->args[1])) {
            return true;
        }

        return false;
    }

    private function refactorStringArgument(MethodCall $methodCall): Node
    {
        // swap arguments
        [$methodCall->args[0], $methodCall->args[1]] = [$methodCall->args[1], $methodCall->args[0]];

        if ($this->isEventNameSameAsEventObjectClass($methodCall)) {
            unset($methodCall->args[1]);
        }

        return $methodCall;
    }

    private function refactorGetCallFuncCall(
        MethodCall $methodCall,
        Expr $secondArgumentValue,
        Expr $firstArgumentValue
    ): ?MethodCall {
        if ($this->isName($secondArgumentValue, 'get_class')) {
            $getClassArgumentValue = $secondArgumentValue->args[0]->value;

            if ($this->areNodesEqual($firstArgumentValue, $getClassArgumentValue)) {
                unset($methodCall->args[1]);

                return $methodCall;
            }
        }

        return null;
    }
}
