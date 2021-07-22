<?php

declare(strict_types=1);

namespace Composer\EventDispatcher;

if (interface_exists(\Composer\EventDispatcher\EventSubscriberInterface::class)) {
    return;
}

interface EventSubscriberInterface
{
}
