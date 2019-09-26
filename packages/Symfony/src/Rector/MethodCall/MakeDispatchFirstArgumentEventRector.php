<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
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
        if (! $this->isObjectType($node, $this->eventDispatcherClass)) {
            return null;
        }

        if (! $this->isName($node, 'dispatch')) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        $secondArgumentValue = $node->args[1]->value;

        if ($this->isStringOrUnionStringOnlyType($firstArgumentValue)) {
            // swap arguments
            [$node->args[0], $node->args[1]] = [$node->args[1], $node->args[0]];

            if ($this->isEventNameSameAsEventObjectClass($node)) {
                unset($node->args[1]);
            }

            return $node;
        }

        if ($secondArgumentValue instanceof FuncCall) {
            if ($this->isName($secondArgumentValue, 'get_class')) {
                $getClassArgument = $secondArgumentValue->args[0]->value;

                if ($this->areNodesEqual($firstArgumentValue, $getClassArgument)) {
                    unset($node->args[1]);
                    return $node;
                }
            }
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
}
