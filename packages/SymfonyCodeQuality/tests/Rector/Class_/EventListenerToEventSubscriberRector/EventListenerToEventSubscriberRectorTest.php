<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector;

use Rector\Configuration\Option;
use Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\Source\ListenersKernel;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EventListenerToEventSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        // wtf: all test have to be in single file due to autoloading race-condigition and container creating issue of fixture
        $this->setParameter(Option::KERNEL_CLASS_PARAMETER, ListenersKernel::class);
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');
    }

    protected function getRectorClass(): string
    {
        return EventListenerToEventSubscriberRector::class;
    }
}
