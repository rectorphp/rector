<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class KeepStringEventConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        /** @var Event|CustomEvent $customEvent */
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch(CustomEvent::NAME, $customEvent);
        $eventDispatcher->dispatch($customEvent, CustomEvent::NAME);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source\CustomEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class KeepStringEventConstant
{
    public function run(EventDispatcher $eventDispatcher)
    {
        /** @var Event|CustomEvent $customEvent */
        $customEvent = new CustomEvent();
        $eventDispatcher->dispatch($customEvent, CustomEvent::NAME);
        $eventDispatcher->dispatch($customEvent, CustomEvent::NAME);
    }
}

?>
