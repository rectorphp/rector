<?php declare(strict_types=1);

namespace Rector\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ClassBasedEventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(Event $event): Event
    {
        $eventClass = get_class($event);
        return $this->eventDispatcher->dispatch($eventClass, $event);
    }
}
