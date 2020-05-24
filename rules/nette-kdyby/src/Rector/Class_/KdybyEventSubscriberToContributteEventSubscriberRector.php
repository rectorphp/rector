<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\Class_;

use Kdyby\Events\Subscriber;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\NodeManipulator\GetSubscribedEventsArrayManipulator;
use Rector\NetteKdyby\NodeManipulator\SubscriberMethodArgumentToContributteEventObjectManipulator;
use Rector\NetteKdyby\NodeResolver\ListeningMethodsCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\NetteKdyby\Tests\Rector\Class_\KdybyEventSubscriberToContributteEventSubscriberRector\KdybyEventSubscriberToContributteEventSubscriberRectorTest
 */
final class KdybyEventSubscriberToContributteEventSubscriberRector extends AbstractRector
{
    /**
     * @var GetSubscribedEventsArrayManipulator
     */
    private $getSubscribedEventsArrayManipulator;

    /**
     * @var SubscriberMethodArgumentToContributteEventObjectManipulator
     */
    private $subscriberMethodArgumentToContributteEventObjectManipulator;

    /**
     * @var ListeningMethodsCollector
     */
    private $listeningMethodsCollector;

    public function __construct(
        GetSubscribedEventsArrayManipulator $getSubscribedEventsArrayManipulator,
        SubscriberMethodArgumentToContributteEventObjectManipulator $subscriberMethodArgumentToContributteEventObjectManipulator,
        ListeningMethodsCollector $listeningMethodsCollector
    ) {
        $this->getSubscribedEventsArrayManipulator = $getSubscribedEventsArrayManipulator;
        $this->subscriberMethodArgumentToContributteEventObjectManipulator = $subscriberMethodArgumentToContributteEventObjectManipulator;
        $this->listeningMethodsCollector = $listeningMethodsCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change EventSubscriber from Kdyby to Contributte', [
            new CodeSample(
                <<<'PHP'
use Kdyby\Events\Subscriber;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;

class GetApplesSubscriber implements Subscriber
{
    public function getSubscribedEvents()
    {
        return [
            Application::class . '::onShutdown',
            CustomService::class . '::onCopy' => 'onCustomCopy',
        ];
    }

    public function onShutdown(Presenter $presenter)
    {
        $presenterName = $presenter->getName();
        // ...
    }

    public function onCustomCopy()
    {
    }
}
PHP
,
                <<<'PHP'
use Contributte\Events\Extra\Event\Application\ShutdownEvent;
use Kdyby\Events\Subscriber;
use Nette\Application\Application;

class GetApplesSubscriber implements Subscriber
{
    public static function getSubscribedEvents()
    {
        return [
            ShutdownEvent::class => 'onShutdown',
            CustomService::class . '::onCopy' => 'onCustomCopy',
        ];
    }

    public function onShutdown(ShutdownEvent $shutdownEvent)
    {
        $presenter = $shutdownEvent->getPresenter();
        $presenterName = $presenter->getName();
        // ...
    }

    public function onCustomCopy()
    {
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        $this->makeStatic($node);

        $this->refactorEventNames($node);

        $listeningClassMethods = $this->listeningMethodsCollector->collectFromClassAndGetSubscribedEventClassMethod(
            $class,
            $node
        );

        $this->subscriberMethodArgumentToContributteEventObjectManipulator->change($listeningClassMethods);

        return $node;
    }

    private function resolveMethodNameFromKdybyEventName(Expr $expr): string
    {
        $kdybyEventName = $this->getValue($expr);
        if (Strings::contains($kdybyEventName, '::')) {
            return (string) Strings::after($kdybyEventName, '::', - 1);
        }

        throw new NotImplementedException($kdybyEventName);
    }

    private function shouldSkipClassMethod($node): bool
    {
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return true;
        }

        if (! $this->isObjectType($class, Subscriber::class)) {
            return true;
        }

        return ! $this->isName($node, 'getSubscribedEvents');
    }

    private function refactorArrayWithEventTable(Array_ $array): void
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem->key !== null) {
                continue;
            }

            $methodName = $this->resolveMethodNameFromKdybyEventName($arrayItem->value);
            $arrayItem->key = $arrayItem->value;
            $arrayItem->value = new String_($methodName);
        }
    }

    private function refactorEventNames(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof Return_) {
                return null;
            }

            if ($node->expr === null) {
                return null;
            }

            $returnedExpr = $node->expr;
            if (! $returnedExpr instanceof Array_) {
                return null;
            }

            $this->refactorArrayWithEventTable($returnedExpr);

            $this->getSubscribedEventsArrayManipulator->change($returnedExpr);
        });
    }
}
