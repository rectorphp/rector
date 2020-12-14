<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
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

namespace Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony4\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
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
