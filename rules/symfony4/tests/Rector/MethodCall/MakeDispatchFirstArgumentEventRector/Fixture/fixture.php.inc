<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDisptacher)
    {
        $eventDisptacher->dispatch('event_name', new Event());

        $eventDisptacher->dispatch(new Event(), 'event_name');
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SomeClass
{
    public function run(EventDispatcherInterface $eventDisptacher)
    {
        $eventDisptacher->dispatch(new Event(), 'event_name');

        $eventDisptacher->dispatch(new Event(), 'event_name');
    }
}

?>
