<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class Fixture
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch('my-event');
    }
}

?>
-----
<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class Fixture
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch(new class implements \League\Event\HasEventName
        {
            public function eventName(): string
            {
                return 'my-event';
            }
        });
    }
}

?>
