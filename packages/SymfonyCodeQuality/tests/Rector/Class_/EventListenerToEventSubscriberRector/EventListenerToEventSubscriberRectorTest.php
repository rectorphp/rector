<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector;

use Rector\Configuration\Option;
use Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EventListenerToEventSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        // wtf: all test have to be in single file due to autoloading race-condigition and container creating issue of fixture
        $this->setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/config/listener_services.xml');

        $this->doTestFile(__DIR__ . '/Fixture/some_listener.php.inc');
        $this->doTestFile(__DIR__ . '/Fixture/with_priority_listener.php.inc');
        $this->doTestFile(__DIR__ . '/Fixture/multiple_listeners.php.inc');
    }

    protected function getRectorClass(): string
    {
        return EventListenerToEventSubscriberRector::class;
    }
}
