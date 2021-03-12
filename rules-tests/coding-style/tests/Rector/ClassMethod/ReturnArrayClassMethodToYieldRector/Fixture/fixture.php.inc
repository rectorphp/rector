<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;

class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;

class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): \Iterator
    {
        yield 'event' => 'callback';
    }
}

?>
