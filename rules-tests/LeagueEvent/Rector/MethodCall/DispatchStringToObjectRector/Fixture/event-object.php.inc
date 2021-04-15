<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class EventObject
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch(new \League\Event\Event('my-event'));
    }
}

?>
