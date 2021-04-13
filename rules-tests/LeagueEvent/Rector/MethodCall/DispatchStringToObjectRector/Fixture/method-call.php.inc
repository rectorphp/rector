<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class MethodCall
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch($this->createEventName());
    }

    private function createEventName(): string
    {
        return 'my-event';
    }
}

?>
-----
<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class MethodCall
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch(new class($this->createEventName()) implements \League\Event\HasEventName
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

    private function createEventName(): string
    {
        return 'my-event';
    }
}

?>
