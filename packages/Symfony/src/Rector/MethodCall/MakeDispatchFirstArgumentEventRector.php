<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/blog/new-in-symfony-4-3-simpler-event-dispatching
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
                <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDisptacher)
    {
        $eventDisptacher->dispatch('event_name', new Event());
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDisptacher)
    {
        $eventDisptacher->dispatch(new Event(), 'event_name');
    }
}
CODE_SAMPLE
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
        if (! $this->isType($node, $this->eventDispatcherClass)) {
            return null;
        }

        if (! $this->isName($node, 'dispatch')) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        if (! $this->isStringyType($node->args[0]->value)) {
            return null;
        }

        // swap arguments
        [$node->args[0], $node->args[1]] = [$node->args[1], $node->args[0]];

        return $node;
    }
}
