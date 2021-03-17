<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventClassConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch(CustomEvent::class, $customEvent);
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Tests\Symfony4\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventClassConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch($customEvent);
    }
}

?>
