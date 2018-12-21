<?php declare(strict_types=1);

namespace Rector\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AutowiredEventDispatcher extends EventDispatcher
{
    /**
     * @param EventSubscriberInterface[] $eventSubscribers
     */
    public function __construct(array $eventSubscribers)
    {
        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addSubscriber($eventSubscriber);
        }
    }
}
