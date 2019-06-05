<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class KeepStringEventConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch(CustomEvent::NAME, $customEvent);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class KeepStringEventConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch($customEvent, CustomEvent::NAME);
    }
}

?>
