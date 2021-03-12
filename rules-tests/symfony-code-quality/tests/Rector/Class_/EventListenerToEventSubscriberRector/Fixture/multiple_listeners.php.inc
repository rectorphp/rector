<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class MultipleMethods
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

class MultipleMethodsEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
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
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return ['single_event' => 'singles', 'multi_event' => ['callMe', 'meToo']];
    }
}

?>
