<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleListeners
{
    public function callMe()
    {
    }

    public function singles()
    {
    }

    public function meToo()
    {
    }
}

?>
-----
<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleListenersEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function callMe()
    {
    }

    public function singles()
    {
    }

    public function meToo()
    {
    }
    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return ['single_event' => 'singles', 'multi_event' => ['callMe', 'meToo']];
    }
}

?>
