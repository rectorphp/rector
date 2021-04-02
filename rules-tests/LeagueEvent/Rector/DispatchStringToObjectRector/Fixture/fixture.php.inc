<?php

namespace Rector\Tests\LeagueEvent\Rector\DispatchStringToObjectRector\Fixture;

class Fixture
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    /** @var \League\Event\Emitter */
    private $emitter;

    public function run()
    {
        $this->dispatcher->dispatch('my-event');
        $this->emitter->dispatch('your-event');
    }
}

?>
-----
<?php

namespace Rector\Tests\LeagueEvent\Rector\DispatchStringToObjectRector\Fixture;

class Fixture
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    /** @var \League\Event\Emitter */
    private $emitter;

    public function run()
    {
        $this->dispatcher->dispatch(new class implements \League\Event\HasEventName
        {
            public function eventName(): string
            {
                return 'my-event';
            }
        });
        $this->emitter->dispatch(new class implements \League\Event\HasEventName
        {
            public function eventName(): string
            {
                return 'your-event';
            }
        });
    }
}

?>
