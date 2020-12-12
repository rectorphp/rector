<?php

declare(strict_types=1);

namespace Rector\Core\EventDispatcher\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated Replace with interface. Remove whole event system to keep 1 less pattern for same thing
 */
final class AfterProcessEvent extends Event
{
}
