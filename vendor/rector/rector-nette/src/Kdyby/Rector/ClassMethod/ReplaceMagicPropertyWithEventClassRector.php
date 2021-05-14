<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Rector\ClassMethod;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\Kdyby\DataProvider\EventAndListenerTreeProvider;
use Rector\Nette\Kdyby\Naming\EventClassNaming;
use Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer;
use Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Kdyby\Rector\ClassMethod\ReplaceMagicPropertyWithEventClassRector\ReplaceMagicPropertyWithEventClassRectorTest
 */
final class ReplaceMagicPropertyWithEventClassRector extends \Rector\Core\Rector\AbstractRector
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
    /**
     * @var GetSubscribedEventsClassMethodAnalyzer
     */
    private $getSubscribedEventsClassMethodAnalyzer;
    public function __construct(\Rector\Nette\Kdyby\DataProvider\EventAndListenerTreeProvider $eventAndListenerTreeProvider, \Rector\Nette\Kdyby\Naming\EventClassNaming $eventClassNaming, \Rector\Nette\Kdyby\NodeManipulator\ListeningClassMethodArgumentManipulator $listeningClassMethodArgumentManipulator, \Rector\Nette\Kdyby\NodeAnalyzer\GetSubscribedEventsClassMethodAnalyzer $getSubscribedEventsClassMethodAnalyzer)
    {
        $this->eventClassNaming = $eventClassNaming;
        $this->listeningClassMethodArgumentManipulator = $listeningClassMethodArgumentManipulator;
        $this->eventAndListenerTreeProvider = $eventAndListenerTreeProvider;
        $this->getSubscribedEventsClassMethodAnalyzer = $getSubscribedEventsClassMethodAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change getSubscribedEvents() from on magic property, to Event class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->getSubscribedEventsClassMethodAnalyzer->detect($node)) {
            return null;
        }
        $this->replaceEventPropertyReferenceWithEventClassReference($node);
        $eventAndListenerTrees = $this->eventAndListenerTreeProvider->provide();
        if ($eventAndListenerTrees === []) {
            return null;
        }
        /** @var string $className */
        $className = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        foreach ($eventAndListenerTrees as $eventAndListenerTree) {
            $this->listeningClassMethodArgumentManipulator->changeFromEventAndListenerTreeAndCurrentClassName($eventAndListenerTree, $className);
        }
        return $node;
    }
    private function replaceEventPropertyReferenceWithEventClassReference(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) {
            if (!$node instanceof \PhpParser\Node\Expr\ArrayItem) {
                return null;
            }
            $arrayKey = $node->key;
            if (!$arrayKey instanceof \PhpParser\Node\Expr) {
                return null;
            }
            $eventPropertyReferenceName = $this->valueResolver->getValue($arrayKey);
            // is property?
            if (!\RectorPrefix20210514\Nette\Utils\Strings::contains($eventPropertyReferenceName, '::')) {
                return null;
            }
            $eventClassName = $this->eventClassNaming->createEventClassNameFromClassPropertyReference($eventPropertyReferenceName);
            $node->key = $this->nodeFactory->createClassConstReference($eventClassName);
            return $node;
        });
    }
}
