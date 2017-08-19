<?php declare(strict_types=1);

namespace Rector\EventSubscriber;

use PhpParser\NodeTraverser;
use Rector\Event\AfterParseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TraverseAfterParsingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(NodeTraverser $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [AfterParseEvent::class => 'afterParse'];
    }

    public function afterParse(AfterParseEvent $afterParseEvent): void
    {
        $nodes = $afterParseEvent->getNodes();
        $nodes = $this->nodeTraverser->traverse($nodes);
        $afterParseEvent->changeNodes($nodes);
    }
}
