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
        $this->dispatcher->dispatch(new class('my-event') implements \League\Event\HasEventName
        {
            private $name;
            public function __construct(string $name)
            {
                $this->name = $name;
            }
            public function eventName(): string
            {
                return $this->name;
            }
        });
    }
}

?>
