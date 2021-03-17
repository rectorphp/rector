<?php

namespace Rector\Tests\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Broadcast
{
    public function action()
    {
        return broadcast('template.blade');
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Broadcast
{
    public function __construct(private \Illuminate\Contracts\Broadcasting\Factory $broadcastingFactory)
    {
    }
    public function action()
    {
        return $this->broadcastingFactory->event('template.blade');
    }
}

?>
