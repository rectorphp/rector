<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Fixture;

class SomeListener
{
    public function methodToBeCalled()
    {
    }
}

class WithPriorityListener
{
    public function callMe()
    {
    }
}

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

class SomeEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function methodToBeCalled()
    {
    }
    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return ['some_event' => 'methodToBeCalled'];
    }
}

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
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return ['single_event' => 'singles', 'multi_event' => ['callMe', 'meToo']];
    }
}

?>
