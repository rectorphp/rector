<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class SomeBroadcastController
{
    private \Illuminate\Contracts\Broadcasting\Factory $broadcastingFactory;
    public function __construct(\Illuminate\Contracts\Broadcasting\Factory $broadcastingFactory)
    {
        $this->broadcastingFactory = $broadcastingFactory;
    }
    public function action()
    {
        return $this->broadcastingFactory->event('template.blade');
    }
}

?>
