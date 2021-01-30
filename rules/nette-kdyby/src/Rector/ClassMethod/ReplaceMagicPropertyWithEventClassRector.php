<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NetteKdyby\DataProvider\EventAndListenerTreeProvider;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\NodeManipulator\ListeningClassMethodArgumentManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteKdyby\Tests\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\ReplaceMagicPropertyWithEventClassRectorTest
 */
final class ReplaceMagicPropertyWithEventClassRector extends AbstractKdybyEventSubscriberRector
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change getSubscribedEvents() from on magic property, to Event class',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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

    private function replaceEventPropertyReferenceWithEventClassReference(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            $arrayKey = $node->key;
            if (! $arrayKey instanceof Expr) {
                return null;
            }

            $eventPropertyReferenceName = $this->valueResolver->getValue($arrayKey);

            // is property?
            if (! Strings::contains($eventPropertyReferenceName, '::')) {
                return null;
            }

            $eventClassName = $this->eventClassNaming->createEventClassNameFromClassPropertyReference(
                $eventPropertyReferenceName
            );
            $node->key = $this->nodeFactory->createClassConstReference($eventClassName);
        });
    }
}
