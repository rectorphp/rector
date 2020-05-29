<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\ClassMethod;

use Kdyby\Events\Subscriber;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\NodeManipulator\SubscriberMethodArgumentToContributteEventObjectManipulator;
use Rector\NetteKdyby\NodeResolver\ListeningMethodsCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRectorTest
 */
final class ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector extends AbstractRector
{
    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    /**
     * @var ListeningMethodsCollector
     */
    private $listeningMethodsCollector;

    /**
     * @var SubscriberMethodArgumentToContributteEventObjectManipulator
     */
    private $subscriberMethodArgumentToContributteEventObjectManipulator;

    public function __construct(
        EventClassNaming $eventClassNaming,
        ListeningMethodsCollector $listeningMethodsCollector,
        SubscriberMethodArgumentToContributteEventObjectManipulator $subscriberMethodArgumentToContributteEventObjectManipulator
    ) {
        $this->eventClassNaming = $eventClassNaming;
        $this->listeningMethodsCollector = $listeningMethodsCollector;
        $this->subscriberMethodArgumentToContributteEventObjectManipulator = $subscriberMethodArgumentToContributteEventObjectManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change getSubscribedEvents() from on magic property, to Event class', [
            new CodeSample(
                <<<'PHP'
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumService::class . '::onApprove' => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(Album $album, int $adminId): void
    {
    }
}
PHP
,
                <<<'PHP'
use Kdyby\Events\Subscriber;

final class ActionLogEventSubscriber implements Subscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            AlbumServiceApproveEvent::class => 'onAlbumApprove',
        ];
    }

    public function onAlbumApprove(AlbumServiceApproveEventAlbum $albumServiceApproveEventAlbum): void
    {
        $album = $albumServiceApproveEventAlbum->getAlbum();
        $adminId = $albumServiceApproveEventAlbum->getAdminId();
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

        $this->replaceEventPropertyReferenceWithEventClassReference($node);

        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);

        $listeningClassMethods = $this->listeningMethodsCollector->collectFromClassAndGetSubscribedEventClassMethod(
            $class,
            $node,
            ListeningMethodsCollector::EVENT_TYPE_CUSTOM
        );

        $this->subscriberMethodArgumentToContributteEventObjectManipulator->change($listeningClassMethods);

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return true;
        }

        if (! $this->isObjectType($class, Subscriber::class)) {
            return true;
        }

        return ! $this->isName($classMethod, 'getSubscribedEvents');
    }

    private function replaceEventPropertyReferenceWithEventClassReference(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            $arrayKey = $node->key;
            if ($arrayKey === null) {
                return null;
            }

            $eventPropertyReferenceName = $this->getValue($arrayKey);

            // is property?
            if (! Strings::contains($eventPropertyReferenceName, '::')) {
                return null;
            }

            $eventClassName = $this->eventClassNaming->createEventClassNameFromClassPropertyReference(
                $eventPropertyReferenceName
            );
            if ($eventClassName === null) {
                return null;
            }

            $node->key = $this->createClassConstantReference($eventClassName);
        });
    }
}
