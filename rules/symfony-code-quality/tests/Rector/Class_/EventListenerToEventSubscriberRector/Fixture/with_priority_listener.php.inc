<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class WithPriorityListener
{
    public function callMe()
    {
    }
}

?>
-----
<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class WithPriorityEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function callMe()
    {
    }
    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return ['some_event' => ['callMe', 1540]];
    }
}

?>
