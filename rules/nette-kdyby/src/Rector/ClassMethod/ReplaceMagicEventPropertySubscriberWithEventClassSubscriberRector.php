<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\DataProvider\EventAndListenerTreeProvider;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\NodeManipulator\ListeningClassMethodArgumentManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector\ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRectorTest
 */
final class ReplaceMagicEventPropertySubscriberWithEventClassSubscriberRector extends AbstractRector
{
    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    /**
     * @var ListeningClassMethodArgumentManipulator
     */
    private $listeningClassMethodArgumentManipulator;

    /**
     * @var EventAndListenerTreeProvider
     */
    private $eventAndListenerTreeProvider;

    public function __construct(
        EventAndListenerTreeProvider $eventAndListenerTreeProvider,
        EventClassNaming $eventClassNaming,
        ListeningClassMethodArgumentManipulator $listeningClassMethodArgumentManipulator
    ) {
        $this->eventClassNaming = $eventClassNaming;
        $this->listeningClassMethodArgumentManipulator = $listeningClassMethodArgumentManipulator;
        $this->eventAndListenerTreeProvider = $eventAndListenerTreeProvider;
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
        $album->play();
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
        $album->play();
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

        $eventAndListenerTrees = $this->eventAndListenerTreeProvider->provide();

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($eventAndListenerTrees as $eventAndListenerTree) {
            $this->listeningClassMethodArgumentManipulator->changeFromEventAndListenerTreeAndCurrentClassName(
                $eventAndListenerTree,
                $className
            );
        }

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        if (! $this->isObjectType($classLike, 'Kdyby\Events\Subscriber')) {
            return true;
        }

        return ! $this->isName($classMethod, 'getSubscribedEvents');
    }

    private function replaceEventPropertyReferenceWithEventClassReference(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node): ?void {
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
