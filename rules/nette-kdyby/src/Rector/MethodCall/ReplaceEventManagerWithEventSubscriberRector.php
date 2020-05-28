<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\EventClassNaming;

/**
 * @see \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\ReplaceEventManagerWithEventSubscriberRectorTest
 */
final class ReplaceEventManagerWithEventSubscriberRector extends AbstractRector
{
    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    public function __construct(EventClassNaming $eventClassNaming)
    {
        $this->eventClassNaming = $eventClassNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change Kdyby EventManager to EventDispatcher', [
            new CodeSample(
                <<<'PHP'
use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
    }
}
PHP
,
                <<<'PHP'
use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatch(new SomeClassCopyEvent($this, $key));
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
        if (! $this->isObjectType($node->var, 'Kdyby\Events\EventManager')) {
            return null;
        }

        if (! $this->isName($node->name, 'dispatchEvent')) {
            return null;
        }

        $node->name = new Identifier('dispatch');

        $oldArgs = $node->args;
        $node->args = [];

        $eventReference = $oldArgs[0]->value;

        $classAndStaticProperty = $this->getValue($eventReference, true);
        $eventCass = $this->eventClassNaming->createEventClassNameFromClassPropertyReference($classAndStaticProperty);

        $args = [];
        if ($oldArgs[1]->value instanceof New_) {
            /** @var New_ $new */
            $new = $oldArgs[1]->value;

            $array = $new->args[0]->value;
            if ($array instanceof Array_) {
                foreach ($array->items as $arrayItem) {
                    $args[] = new Arg($arrayItem->value);
                }
            }
        }

        $class = new New_(new FullyQualified($eventCass), $args);
        $node->args[] = new Arg($class);

        return $node;
    }
}
