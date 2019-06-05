<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\MakeDispatchFirstArgumentEventRector\Source;

use Symfony\Contracts\EventDispatcher\Event;

final class CustomEvent extends Event
{
    public const NAME = 'custom_event';
}
