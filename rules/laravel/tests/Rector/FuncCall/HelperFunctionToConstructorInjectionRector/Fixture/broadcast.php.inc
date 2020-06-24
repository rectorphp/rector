<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBroadcastController
{
    public function action()
    {
        return broadcast('template.blade');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBroadcastController
{
    private \Illuminate\Contracts\Broadcasting\Factory $broadcastFactory;
    public function __construct(\Illuminate\Contracts\Broadcasting\Factory $broadcastFactory)
    {
        $this->broadcastFactory = $broadcastFactory;
    }
    public function action()
    {
        return $this->broadcastFactory->event('template.blade');
    }
}

?>
