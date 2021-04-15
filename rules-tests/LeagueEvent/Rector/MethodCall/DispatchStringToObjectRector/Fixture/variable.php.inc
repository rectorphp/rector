<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class Variable
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $name = 'my-event';
        $this->dispatcher->dispatch($name);
    }
}

?>
-----
<?php

namespace Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\Fixture;

class Variable
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $name = 'my-event';
        $this->dispatcher->dispatch(new class($name) implements \League\Event\HasEventName
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
